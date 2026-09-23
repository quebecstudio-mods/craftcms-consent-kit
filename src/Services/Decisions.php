<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Services;

use Craft;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\web\Request as WebRequest;
use DateTimeImmutable;
use QuebecStudioMods\ConsentKit\Core\Decision;
use QuebecStudioMods\ConsentKit\CraftCms\Db\Table;
use QuebecStudioMods\ConsentKit\CraftCms\Models\Settings;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use yii\base\Component;

/**
 * The consent register: one row per decision, pointing at the screen it was
 * made on. Everything but the answer itself comes from the server, and the
 * answer is checked against the site's inventory before it is kept.
 */
class Decisions extends Component
{
    public const ACTIONS = ['accept-all', 'refuse-all', 'save'];

    public const ORIGINS = ['banner', 'dialog', 'gpc'];

    public const CACHE_ANY = 'cookie-consent-kit:registry:any';

    /** Columns a reader may sort on. Anything else falls back to the date. */
    public const SORTABLE = ['decidedAt', 'siteHandle', 'action', 'outcome'];

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
        if (!Craft::$app->getUser()->checkPermission('cookieConsentKit:viewRegistry')) {
            return false;
        }

        return $this->isEnabled() || $this->hasRecords();
    }

    /** Cached: the navigation is rendered on every control panel page. */
    public function hasRecords(): bool
    {
        $cache = Craft::$app->getCache();
        $known = $cache->get(self::CACHE_ANY);

        if ($known !== false) {
            return (bool)$known;
        }

        $any = (new Query())->from([Table::DECISIONS])->exists();
        $cache->set(self::CACHE_ANY, $any ? 1 : 0, 300);

        return $any;
    }

    /**
     * Records one decision and returns its id, or null when the answer does
     * not describe a screen this site could have shown.
     */
    public function record(int $siteId, string $action, string $origin, array $categories): ?int
    {
        $site = Craft::$app->getSites()->getSiteById($siteId);

        if (!$this->isCollecting() || !$site || !in_array($action, self::ACTIONS, true) || !in_array($origin, self::ORIGINS, true)) {
            return null;
        }

        $config = Plugin::getInstance()->consent->resolvedConfigFor($siteId);
        $answer = Decision::reconcile($categories, $config['categories'] ?? []);

        if ($answer === null) {
            return null;
        }

        $request = Craft::$app->getRequest();

        $context = $this->settings()->registryRequestContext && $request instanceof WebRequest;

        Db::insert(Table::DECISIONS, [
            'presentationId' => Plugin::getInstance()->presentations->idFor($siteId),
            'siteId' => $site->id,
            'siteHandle' => $site->handle,
            'language' => (string)($config['language'] ?? ''),
            'decidedAt' => Db::prepareDateForDb(new DateTimeImmutable()),
            'action' => $action,
            'origin' => $origin,
            'categories' => Json::encode($answer),
            'outcome' => Decision::outcome($answer, $config['categories'] ?? []),
            'consentVersion' => (int)($config['version'] ?? 1),
            'policyUrl' => $config['policyUrl'] ?? null,
            'userId' => $this->settings()->registryUser ? Craft::$app->getUser()->getId() : null,
            'ip' => $context ? $request->getUserIP() : null,
            'userAgent' => $context ? substr((string)$request->getUserAgent(), 0, 512) : null,
        ]);

        Craft::$app->getCache()->set(self::CACHE_ANY, 1, 300);

        return (int)Craft::$app->getDb()->getLastInsertID(Craft::$app->getDb()->getSchema()->getRawTableName(Table::DECISIONS));
    }

    /**
     * A page of the register, newest first, each row carrying its
     * presentation's hash.
     *
     * @return array{rows: array, total: int}
     */
    public function page(array $filters = [], int $page = 1, int $perPage = 50, string $sort = 'decidedAt', string $dir = 'desc'): array
    {
        $sort = in_array($sort, self::SORTABLE, true) ? $sort : 'decidedAt';
        $dir = strtolower($dir) === 'asc' ? SORT_ASC : SORT_DESC;

        $query = $this->filtered($filters);
        $total = (int)$query->count();

        $rows = $this->filtered($filters)
            ->select([
                'd.id',
                'd.siteHandle',
                'd.language',
                'd.decidedAt',
                'd.action',
                'd.origin',
                'd.categories',
                'd.consentVersion',
                'd.outcome',
                'd.presentationId',
                'd.siteId',
                'd.userId',
                'p.hash',
            ])
            ->leftJoin(['p' => Table::PRESENTATIONS], '[[p.id]] = [[d.presentationId]]')
            ->orderBy(["d.$sort" => $dir, 'd.id' => SORT_DESC])
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
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

        $found = [];

        foreach ((new Query())->select(['hash', 'payload'])->from([Table::PRESENTATIONS])->where(['hash' => $hashes])->all() as $row) {
            $payload = Json::decodeIfJson($row['payload']);

            if (is_array($payload)) {
                $found[(string)$row['hash']] = $payload;
            }
        }

        return $found;
    }

    /** One decision with the wording it was made on, or null. */
    public function find(int $id): ?array
    {
        $row = (new Query())
            ->from(['d' => Table::DECISIONS])
            ->select(['d.*', 'p.hash', 'p.payload'])
            ->leftJoin(['p' => Table::PRESENTATIONS], '[[p.id]] = [[d.presentationId]]')
            ->where(['d.id' => $id])
            ->one();

        return $row ?: null;
    }

    /** @return Query<int, array> */
    private function filtered(array $filters): Query
    {
        $query = (new Query())->from(['d' => Table::DECISIONS]);

        if (!empty($filters['siteId'])) {
            $query->andWhere(['d.siteId' => (int)$filters['siteId']]);
        }

        if (!empty($filters['outcome']) && in_array($filters['outcome'], Decision::OUTCOMES, true)) {
            $query->andWhere(['d.outcome' => $filters['outcome']]);
        }

        if (!empty($filters['from'])) {
            $query->andWhere(['>=', 'd.decidedAt', $filters['from']]);
        }

        if (!empty($filters['to'])) {
            $query->andWhere(['<=', 'd.decidedAt', $filters['to']]);
        }

        return $query;
    }

    /** Switching collection on takes the Pro edition. Nothing else does. */
    public function canEnable(): bool
    {
        return Plugin::getInstance()->is(Plugin::EDITION_PRO);
    }

    public function total(): int
    {
        return (int)(new Query())->from([Table::DECISIONS])->count();
    }

    public function oldest(): ?string
    {
        $date = (new Query())->from([Table::DECISIONS])->min('decidedAt');

        return $date === null || $date === false ? null : (string)$date;
    }

    /**
     * Deletes records decided more than `$months` ago, or all of them when
     * `$months` is zero. Returns how many went.
     */
    public function purgeOlderThan(int $months): int
    {
        $condition = $months > 0
            ? ['<', 'decidedAt', Db::prepareDateForDb((new DateTimeImmutable())->modify("-$months months"))]
            : ['not', ['id' => null]];

        $deleted = Db::delete(Table::DECISIONS, $condition);

        $this->dropOrphanPresentations();
        Craft::$app->getCache()->delete(self::CACHE_ANY);

        return $deleted;
    }

    /** Drops outlived records, then the presentations nothing cites any more. */
    public function purge(): int
    {
        $settings = $this->settings();

        if ($settings->registryGrace <= 0) {
            return 0;
        }

        $cutoff = (new DateTimeImmutable())
            ->modify('-' . $settings->cookieMaxAge . ' seconds')
            ->modify('-' . $settings->registryGrace . ' months');

        $deleted = Db::delete(Table::DECISIONS, ['<', 'decidedAt', Db::prepareDateForDb($cutoff)]);

        $this->dropOrphanPresentations();

        return $deleted;
    }

    private function dropOrphanPresentations(): void
    {
        $cited = (new Query())->select(['presentationId'])->from([Table::DECISIONS]);

        Db::delete(Table::PRESENTATIONS, ['not in', 'id', $cited]);
    }

    private function settings(): Settings
    {
        /** @var Settings */
        return Plugin::getInstance()->getSettings();
    }
}
