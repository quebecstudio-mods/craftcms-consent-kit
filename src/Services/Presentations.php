<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Services;

use Craft;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\Json;
use QuebecStudioMods\ConsentKit\Core\Presentation;
use QuebecStudioMods\ConsentKit\CraftCms\Db\Table;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use RuntimeException;
use Throwable;
use yii\base\Component;

/**
 * What the banner was showing, stored and looked up by its fingerprint.
 *
 * The fingerprint itself comes from the core, so it is the same in every
 * integration of the suite and stays recomputable without the plugin.
 */
class Presentations extends Component
{
    public function payloadFor(int $siteId): array
    {
        return Presentation::payload(Plugin::getInstance()->consent->resolvedConfigFor($siteId));
    }

    public function hash(array $payload): string
    {
        return Presentation::hash($payload);
    }

    public function canonical(array $payload): string
    {
        return Presentation::canonical($payload);
    }

    /** The stored presentation for a site now, inserted the first time it is seen. */
    public function idFor(int $siteId): int
    {
        $payload = $this->payloadFor($siteId);
        $hash = $this->hash($payload);

        $id = $this->findId($hash);

        if ($id !== null) {
            return $id;
        }

        try {
            Db::insert(Table::PRESENTATIONS, [
                'hash' => $hash,
                'language' => $payload['language'],
                'payload' => Json::encode($payload),
            ]);
        } catch (Throwable $e) {

            return $this->findId($hash) ?? throw $e;
        }

        return $this->findId($hash) ?? throw new RuntimeException('The presentation could not be stored.');
    }

    private function findId(string $hash): ?int
    {
        $id = (new Query())
            ->select(['id'])
            ->from([Table::PRESENTATIONS])
            ->where(['hash' => $hash])
            ->scalar(Craft::$app->getDb());

        return $id === false || $id === null ? null : (int)$id;
    }
}
