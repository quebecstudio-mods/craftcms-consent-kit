<?php

declare(strict_types=1);

namespace QuebecStudioMods\ConsentKit\CraftCms\Services;

use function CraftCms\Cms\currentUser;

use CraftCms\Cms\Support\Facades\Sites;
use DateTimeImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use QuebecStudioMods\ConsentKit\Core\Decision;
use QuebecStudioMods\ConsentKit\Core\Registry;
use QuebecStudioMods\ConsentKit\CraftCms\Db\Table;
use QuebecStudioMods\ConsentKit\CraftCms\Models\Settings;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;

/**
 * The consent register: one row per decision, pointing at the screen it was
 * made on. Everything but the answer itself comes from the server, and the
 * answer is checked against the site's inventory before it is kept.
 */
class Decisions
{
    public const string CACHE_ANY = 'cookie-consent-kit:registry:any';

    public function __construct(
        private readonly Consent $consent,
        private readonly Presentations $presentations,
    ) {
    }

    /** What the administrator asked for, whatever the edition allows. */
    public function isEnabled(): bool
    {
        return $this->settings()->registry;
    }

    /** Whether decisions are actually being written. */
    public function isCollecting(): bool
    {
        return $this->isEnabled() && Plugin::getInstance()->is(Plugin::EDITION_PRO);
    }

    /** Asked for but not licensed. */
    public function isSuspended(): bool
    {
        return $this->isEnabled() && !$this->isCollecting();
    }

    /** In the navigation while it collects, and while it holds anything. */
    public function isVisible(): bool
    {
        if (!Gate::check('cookieConsentKit:viewRegistry')) {
            return false;
        }

        return $this->isEnabled() || $this->hasRecords();
    }

    /** Cached: the navigation is rendered on every control panel page. */
    public function hasRecords(): bool
    {
        return (bool) Cache::remember(
            self::CACHE_ANY,
            300,
            fn (): int => DB::table(Table::DECISIONS)->exists() ? 1 : 0,
        );
    }

    /**
     * Records one decision and returns its id, or null when the answer does
     * not describe a screen this site could have shown.
     */
    public function record(int $siteId, string $action, string $origin, array $categories): ?int
    {
        $site = Sites::getSiteById($siteId);

        if (!$this->isCollecting() || !$site || !in_array($action, Decision::ACTIONS, true) || !in_array($origin, Decision::ORIGINS, true)) {
            return null;
        }

        $config = $this->consent->resolvedConfigFor($siteId);
        $answer = Decision::reconcile($categories, $config['categories'] ?? []);

        if ($answer === null) {
            return null;
        }

        $context = $this->settings()->registryRequestContext;
        $now = now();

        $id = DB::table(Table::DECISIONS)->insertGetId([
            'presentationId' => $this->presentations->idFor($siteId),
            'siteId' => $site->id,
            'siteHandle' => $site->handle,
            'language' => (string) ($config['language'] ?? ''),
            'decidedAt' => new DateTimeImmutable(),
            'action' => $action,
            'origin' => $origin,
            'categories' => json_encode($answer, JSON_THROW_ON_ERROR),
            'outcome' => Decision::outcome($answer, $config['categories'] ?? []),
            'consentVersion' => (int) ($config['version'] ?? 1),
            'policyUrl' => $config['policyUrl'] ?? null,
            'userId' => $this->settings()->registryUser ? currentUser()?->getCraftUserId() : null,
            'ip' => $context ? request()->ip() : null,
            'userAgent' => $context ? substr((string) request()->userAgent(), 0, 512) : null,
            'dateCreated' => $now,
            'dateUpdated' => $now,
            'uid' => (string) str()->uuid(),
        ]);

        Cache::put(self::CACHE_ANY, 1, 300);

        return $id;
    }

    /**
     * A page of the register, newest first, each row carrying its
     * presentation's hash.
     *
     * @return array{rows: array, total: int}
     */
    public function page(array $filters = [], int $page = 1, int $perPage = 50, string $sort = 'decidedAt', string $dir = 'desc'): array
    {
        $sort = in_array($sort, Registry::SORTABLE, true) ? $sort : 'decidedAt';
        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';
        $total = $this->filtered($filters)->count();

        $rows = $this->filtered($filters)
            ->leftJoin(Table::PRESENTATIONS.' as p', 'p.id', '=', 'd.presentationId')
            ->select([
                'd.id',
                'd.siteHandle',
                'd.siteId',
                'd.userId',
                'd.language',
                'd.decidedAt',
                'd.action',
                'd.origin',
                'd.categories',
                'd.outcome',
                'd.consentVersion',
                'p.hash',
            ])
            ->orderBy("d.$sort", $dir)
            ->orderByDesc('d.id')
            ->forPage($page, $perPage)
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * Stored presentations, by hash.
     *
     * @return array<string, array>
     */
    public function presentationsByHash(array $hashes): array
    {
        $hashes = array_values(array_filter($hashes));

        if ($hashes === []) {
            return [];
        }

        return DB::table(Table::PRESENTATIONS)
            ->select(['hash', 'payload'])
            ->whereIn('hash', $hashes)
            ->get()
            ->mapWithKeys(fn (object $row): array => [
                (string) $row->hash => json_decode((string) $row->payload, true) ?: [],
            ])
            ->all();
    }

    /** One decision with the wording it was made on, or null. */
    public function find(int $id): ?array
    {
        $row = DB::table(Table::DECISIONS.' as d')
            ->leftJoin(Table::PRESENTATIONS.' as p', 'p.id', '=', 'd.presentationId')
            ->select(['d.*', 'p.hash', 'p.payload'])
            ->where('d.id', $id)
            ->first();

        return $row ? (array) $row : null;
    }

    public function total(): int
    {
        return DB::table(Table::DECISIONS)->count();
    }

    public function oldest(): ?string
    {
        $date = DB::table(Table::DECISIONS)->min('decidedAt');

        return $date === null ? null : (string) $date;
    }

    /**
     * Deletes records decided more than `$months` ago, or all of them when
     * `$months` is zero. Returns how many went.
     */
    public function purgeOlderThan(int $months): int
    {
        $query = DB::table(Table::DECISIONS);

        if ($months > 0) {
            $query->where('decidedAt', '<', (new DateTimeImmutable())->modify("-$months months"));
        }

        $deleted = $query->delete();

        $this->dropOrphanPresentations();
        Cache::forget(self::CACHE_ANY);

        return $deleted;
    }

    /** Drops outlived records, then the presentations nothing cites any more. */
    public function purge(): int
    {
        $settings = $this->settings();
        $cutoff = Registry::cutoff($settings->cookieMaxAge, $settings->registryGrace);

        if ($cutoff === null) {
            return 0;
        }

        $deleted = DB::table(Table::DECISIONS)->where('decidedAt', '<', $cutoff)->delete();

        $this->dropOrphanPresentations();

        return $deleted;
    }

    /** Switching collection on takes the Pro edition. Nothing else does. */
    public function canEnable(): bool
    {
        return Plugin::getInstance()->is(Plugin::EDITION_PRO);
    }

    private function filtered(array $filters): Builder
    {
        $query = DB::table(Table::DECISIONS.' as d');

        if (!empty($filters['siteId'])) {
            $query->where('d.siteId', (int) $filters['siteId']);
        }

        if (!empty($filters['outcome']) && in_array($filters['outcome'], Decision::OUTCOMES, true)) {
            $query->where('d.outcome', $filters['outcome']);
        }

        if (!empty($filters['from'])) {
            $query->where('d.decidedAt', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->where('d.decidedAt', '<=', $filters['to']);
        }

        return $query;
    }

    private function dropOrphanPresentations(): void
    {
        DB::table(Table::PRESENTATIONS)
            ->whereNotIn('id', DB::table(Table::DECISIONS)->select('presentationId'))
            ->delete();
    }

    private function settings(): Settings
    {
        /** @var Settings */
        return Plugin::getInstance()->getSettings();
    }
}
