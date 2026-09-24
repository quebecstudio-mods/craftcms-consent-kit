<?php

declare(strict_types=1);

namespace QuebecStudioMods\ConsentKit\CraftCms\Controllers;

use CraftCms\Cms\Auth\Concerns\EnforcesPermissions;
use CraftCms\Cms\Entry\Elements\Entry;
use CraftCms\Cms\Http\RespondsWithFlash;
use CraftCms\Cms\Http\Responses\CpScreenResponse;
use CraftCms\Cms\Support\Facades\Entries;
use CraftCms\Cms\Support\Facades\Sites;
use CraftCms\Cms\Support\Facades\Users;
use CraftCms\Cms\Support\Url;

use function CraftCms\Cms\t;

use CraftCms\Cms\User\Elements\User;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use QuebecStudioMods\ConsentKit\Core\Decision;
use QuebecStudioMods\ConsentKit\Core\Registry;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Consent;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Decisions;
use QuebecStudioMods\ConsentKit\CraftCms\Web\Spreadsheet;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reading the register from the control panel.
 *
 * Every action is permission-gated rather than admin-only: producing a proof
 * is the job of whoever answers for the site's privacy, who has no business in
 * its settings.
 */
final class RegistryController
{
    use EnforcesPermissions;
    use RespondsWithFlash;

    private const int PER_PAGE = 50;

    private const int BATCH = 500;

    public function __construct(
        private readonly Decisions $decisions,
        private readonly Consent $consent,
    ) {
    }

    public function index(Request $request): CpScreenResponse
    {
        $this->requirePermission('cookieConsentKit:viewRegistry');

        $filters = $this->filters($request);
        $page = max(1, (int) $request->query('page'));
        $sort = (string) $request->query('sort', 'decidedAt');
        $dir = (string) $request->query('dir', 'desc');
        $result = $this->decisions->page($filters, $page, self::PER_PAGE, $sort, $dir);

        return new CpScreenResponse()
            ->title(t('Consent register', category: 'cookie-consent-kit'))
            ->crumbs([['label' => t('Consent', category: 'cookie-consent-kit')]])
            ->contentTemplate('cookie-consent-kit/registry/index', [
                'rows' => $result['rows'],
                'users' => $this->usersIn($result['rows']),
                'outcomes' => Decision::OUTCOMES,
                'sort' => in_array($sort, Registry::SORTABLE, true) ? $sort : 'decidedAt',
                'dir' => strtolower($dir) === 'asc' ? 'asc' : 'desc',
                'total' => $result['total'],
                'page' => $page,
                'pages' => (int) ceil($result['total'] / self::PER_PAGE),
                'filters' => $filters,
                'sites' => Sites::getAllSites()->values()->all(),
                'multisite' => Sites::getAllSites()->count() > 1,
                'collecting' => $this->decisions->isCollecting(),
                'suspended' => $this->decisions->isSuspended(),
                'canExport' => Gate::check('cookieConsentKit:exportRegistry'),
            ]);
    }

    public function detail(int $id): CpScreenResponse
    {
        $this->requirePermission('cookieConsentKit:viewRegistry');

        $decision = $this->decisions->find($id);

        abort_if($decision === null, 404);

        return new CpScreenResponse()
            ->title(t('Decision {id}', ['id' => $id], category: 'cookie-consent-kit'))
            ->crumbs([[
                'label' => t('Consent register', category: 'cookie-consent-kit'),
                'href' => Url::cpUrl('cookie-consent-kit/registry'),
            ]])
            ->contentTemplate('cookie-consent-kit/registry/detail', [
                'decision' => $decision,
                'user' => $decision['userId'] ? Users::getUserById((int) $decision['userId']) : null,
                'policyEntry' => $this->policyEntryFor($decision),
                'site' => $decision['siteId'] ? Sites::getSiteById((int) $decision['siteId']) : null,
                'multisite' => Sites::getAllSites()->count() > 1,
                'answer' => json_decode((string) $decision['categories'], true) ?: [],
                'presentation' => json_decode((string) ($decision['payload'] ?? ''), true) ?: [],
            ]);
    }

    public function export(Request $request): Response
    {
        $this->requirePermission('cookieConsentKit:exportRegistry');

        return match ($request->query('format')) {
            'json' => $this->exportJson($request),
            'xlsx' => $this->exportSpreadsheet($request, 'xlsx'),
            default => $this->exportSpreadsheet($request, 'csv'),
        };
    }

    public function purge(Request $request): RedirectResponse
    {
        $this->requirePermission('cookieConsentKit:purgeRegistry');

        $deleted = $this->decisions->purgeOlderThan(max(0, (int) $request->input('months', 0)));

        return back()->with('success', t('{count} records deleted.', ['count' => $deleted], category: 'cookie-consent-kit'));
    }

    /**
     * CSV and XLSX come from the same rows. Craft's spreadsheet writer is not
     * exposed as a response format here, so the file is built directly.
     */
    private function exportSpreadsheet(Request $request, string $format): Response
    {
        $rows = [];

        foreach ($this->batches($request) as $batch) {
            foreach ($batch as $row) {
                $rows[] = $this->flatten($row);
            }
        }

        return $format === 'xlsx'
            ? Spreadsheet::xlsx($rows, 'consent-register.xlsx')
            : Spreadsheet::csv($rows, 'consent-register.csv');
    }

    /**
     * The decisions, plus the wording of every screen they cite, deduplicated
     * the way the register stores it.
     */
    private function exportJson(Request $request): Response
    {
        $decisions = [];
        $screens = [];

        foreach ($this->batches($request) as $batch) {
            foreach ($batch as $row) {
                $decisions[] = $this->flatten($row, decode: true);
                $screens[$row['hash']] = null;
            }
        }

        foreach ($this->decisions->presentationsByHash(array_keys($screens)) as $hash => $payload) {
            $screens[$hash] = $payload;
        }

        $export = [

            'fingerprint' => [
                'algorithm' => 'sha256',
                'input' => 'JSON of the screen, object keys sorted recursively, list order preserved, unescaped unicode and slashes',
            ],
            'exportedAt' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            'decisions' => $decisions,
            'screens' => array_filter($screens),
        ];

        return response()->streamDownload(
            fn () => print json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'consent-register.json',
            ['Content-Type' => 'application/json'],
        );
    }

    private function flatten(array $row, bool $decode = false): array
    {
        return [
            'id' => (int) $row['id'],
            'decidedAt' => $row['decidedAt'],
            'site' => $row['siteHandle'],
            'language' => $row['language'],
            'action' => $row['action'],
            'origin' => $row['origin'],
            'granted' => $row['outcome'],
            'categories' => $decode ? json_decode((string) $row['categories'], true) : $row['categories'],
            'consentVersion' => (int) $row['consentVersion'],
            'screen' => $row['hash'],
        ];
    }

    /**
     * The filtered register in batches, honouring the requested limit.
     *
     * @return iterable<array>
     */
    private function batches(Request $request): iterable
    {
        $filters = $this->filters($request);
        $limit = max(0, (int) $request->query('limit'));
        $taken = 0;
        $page = 1;

        do {
            $size = $limit > 0 ? min(self::BATCH, $limit - $taken) : self::BATCH;
            $batch = $this->decisions->page($filters, $page, $size)['rows'];

            if ($batch !== []) {
                yield $batch;
            }

            $taken += count($batch);
            $page++;
        } while ($batch !== [] && ($limit === 0 || $taken < $limit));
    }

    /** Signed-in users on a page, loaded once each rather than row by row. */
    private function usersIn(array $rows): array
    {
        $ids = array_values(array_unique(array_filter(array_column($rows, 'userId'))));

        return $ids === [] ? [] : User::find()->id($ids)->get()->keyBy('id')->all();
    }

    /**
     * The policy entry, only when it still serves the URL that was recorded.
     * Anything else would point a reader at a document the decision never saw.
     */
    private function policyEntryFor(array $decision): ?Entry
    {
        if (!$decision['policyUrl'] || !$decision['siteId']) {
            return null;
        }

        $siteId = (int) $decision['siteId'];
        $id = $this->consent->resolver($siteId)->policyEntryId();
        $entry = $id ? Entries::getEntryById($id, $siteId) : null;

        return $entry?->getUrl() === $decision['policyUrl'] ? $entry : null;
    }

    private function filters(Request $request): array
    {
        return array_filter([
            'siteId' => (int) $request->query('siteId'),
            'outcome' => (string) $request->query('outcome', ''),
            'from' => (string) $request->query('from', ''),
            'to' => (string) $request->query('to', ''),
        ]);
    }
}
