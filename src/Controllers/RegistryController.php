<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Controllers;

use Craft;
use craft\elements\Entry;
use craft\elements\User;
use craft\helpers\Json;
use craft\web\Controller;
use craft\web\Response as CraftResponse;
use DateTimeImmutable;
use DateTimeInterface;
use QuebecStudioMods\ConsentKit\Core\Decision;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Decisions;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Reading the register from the control panel.
 *
 * Every action is permission-gated rather than admin-only: producing a proof
 * is the job of whoever answers for the site's privacy, who has no business in
 * its settings.
 */
class RegistryController extends Controller
{
    private const PER_PAGE = 50;

    private const BATCH = 500;

    public function actionIndex(): Response
    {
        $this->requirePermission('cookieConsentKit:viewRegistry');

        $filters = $this->filters();
        $page = max(1, (int)$this->request->getQueryParam('page', 1));
        $sort = (string)$this->request->getQueryParam('sort', 'decidedAt');
        $dir = (string)$this->request->getQueryParam('dir', 'desc');
        $registry = Plugin::getInstance()->decisions;
        $result = $registry->page($filters, $page, self::PER_PAGE, $sort, $dir);

        return $this->renderTemplate('cookie-consent-kit/registry/index', [
            'rows' => $result['rows'],
            'users' => $this->usersIn($result['rows']),
            'outcomes' => Decision::OUTCOMES,
            'sort' => in_array($sort, Decisions::SORTABLE, true) ? $sort : 'decidedAt',
            'dir' => strtolower($dir) === 'asc' ? 'asc' : 'desc',
            'total' => $result['total'],
            'page' => $page,
            'pages' => (int)ceil($result['total'] / self::PER_PAGE),
            'filters' => $filters,
            'sites' => Craft::$app->getSites()->getAllSites(),
            'multisite' => Craft::$app->getIsMultiSite(),
            'collecting' => $registry->isCollecting(),
            'suspended' => $registry->isSuspended(),
            'canExport' => Craft::$app->getUser()->checkPermission('cookieConsentKit:exportRegistry'),
        ]);
    }

    public function actionDetail(int $id): Response
    {
        $this->requirePermission('cookieConsentKit:viewRegistry');

        $decision = Plugin::getInstance()->decisions->find($id);

        if (!$decision) {
            throw new NotFoundHttpException('Decision not found.');
        }

        return $this->renderTemplate('cookie-consent-kit/registry/detail', [
            'decision' => $decision,
            'user' => $decision['userId'] ? Craft::$app->getUsers()->getUserById((int)$decision['userId']) : null,
            'policyEntry' => $this->policyEntryFor($decision),
            'site' => $decision['siteId'] ? Craft::$app->getSites()->getSiteById((int)$decision['siteId']) : null,
            'multisite' => Craft::$app->getIsMultiSite(),
            'answer' => Json::decodeIfJson($decision['categories']) ?: [],
            'presentation' => Json::decodeIfJson($decision['payload'] ?? '') ?: [],
        ]);
    }

    public function actionExport(): Response
    {
        $this->requirePermission('cookieConsentKit:exportRegistry');

        return match ($this->request->getQueryParam('format')) {
            'json' => $this->exportJson(),
            'xlsx' => $this->exportSpreadsheet(CraftResponse::FORMAT_XLSX, 'consent-register.xlsx'),
            default => $this->exportSpreadsheet(CraftResponse::FORMAT_CSV, 'consent-register.csv'),
        };
    }

    /**
     * CSV and XLSX come from the same rows; Craft's own formatters turn them
     * into a file, so a real workbook costs no extra dependency.
     */
    private function exportSpreadsheet(string $format, string $filename): Response
    {
        $rows = [];

        foreach ($this->batches() as $batch) {
            foreach ($batch as $row) {
                $rows[] = [
                    'id' => (int)$row['id'],
                    'decidedAt' => $row['decidedAt'],
                    'site' => $row['siteHandle'],
                    'language' => $row['language'],
                    'action' => $row['action'],
                    'origin' => $row['origin'],
                    'granted' => $row['outcome'],
                    'categories' => $row['categories'],
                    'consentVersion' => (int)$row['consentVersion'],
                    'screen' => $row['hash'],
                ];
            }
        }

        $this->response->data = $rows;
        $this->response->format = $format;
        $this->response->setDownloadHeaders($filename);

        return $this->response;
    }

    /**
     * The decisions, plus the wording of every screen they cite, deduplicated
     * the way the register stores it.
     */
    private function exportJson(): Response
    {
        $decisions = [];
        $screens = [];

        foreach ($this->batches() as $batch) {
            foreach ($batch as $row) {
                $decisions[] = [
                    'id' => (int)$row['id'],
                    'decidedAt' => $row['decidedAt'],
                    'site' => $row['siteHandle'],
                    'language' => $row['language'],
                    'action' => $row['action'],
                    'origin' => $row['origin'],
                    'granted' => $row['outcome'],
                    'categories' => Json::decodeIfJson($row['categories']),
                    'consentVersion' => (int)$row['consentVersion'],
                    'screen' => $row['hash'],
                ];

                $screens[$row['hash']] = null;
            }
        }

        foreach (Plugin::getInstance()->decisions->presentationsByHash(array_keys($screens)) as $hash => $payload) {
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

        return $this->response->sendContentAsFile(
            (string)json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'consent-register.json',
            ['mimeType' => 'application/json'],
        );
    }

    public function actionPurge(): ?Response
    {
        $this->requirePostRequest();
        $this->requirePermission('cookieConsentKit:purgeRegistry');

        $deleted = Plugin::getInstance()->decisions->purgeOlderThan(
            max(0, (int)$this->request->getBodyParam('months', 0))
        );

        $this->setSuccessFlash(Craft::t('cookie-consent-kit', '{count} records deleted.', ['count' => $deleted]));

        return $this->redirectToPostedUrl();
    }

    /**
     * The filtered register in batches, honouring the requested limit. A
     * register worth keeping outgrows what fits in memory at once.
     *
     * @return iterable<array>
     */
    private function batches(): iterable
    {
        $filters = $this->filters();
        $registry = Plugin::getInstance()->decisions;
        $limit = max(0, (int)$this->request->getQueryParam('limit', 0));
        $taken = 0;
        $page = 1;

        do {
            $size = $limit > 0 ? min(self::BATCH, $limit - $taken) : self::BATCH;
            $batch = $registry->page($filters, $page, $size)['rows'];

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

        if ($ids === []) {
            return [];
        }

        $users = [];

        foreach (User::find()->id($ids)->status(null)->all() as $user) {
            $users[$user->id] = $user;
        }

        return $users;
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

        $id = Plugin::getInstance()->consent->policyEntryFor((int)$decision['siteId']);

        if (!$id) {
            return null;
        }

        $entry = Craft::$app->getEntries()->getEntryById($id, (int)$decision['siteId']);

        return $entry?->getUrl() === $decision['policyUrl'] ? $entry : null;
    }

    private function filters(): array
    {
        return array_filter([
            'siteId' => (int)$this->request->getQueryParam('siteId', 0),
            'outcome' => (string)$this->request->getQueryParam('outcome', ''),
            'from' => (string)$this->request->getQueryParam('from', ''),
            'to' => (string)$this->request->getQueryParam('to', ''),
        ]);
    }
}
