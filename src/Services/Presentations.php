<?php

declare(strict_types=1);

namespace QuebecStudioMods\ConsentKit\CraftCms\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use QuebecStudioMods\ConsentKit\Core\Presentation;
use QuebecStudioMods\ConsentKit\CraftCms\Db\Table;
use RuntimeException;

/**
 * What the banner was showing, stored and looked up by its fingerprint.
 *
 * The fingerprint itself comes from the core, so it is the same in every
 * integration of the suite and stays recomputable without the plugin.
 */
class Presentations
{
    public function __construct(private readonly Consent $consent)
    {
    }

    public function payloadFor(int $siteId): array
    {
        return Presentation::payload($this->consent->resolvedConfigFor($siteId));
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

        $now = now();

        try {
            DB::table(Table::PRESENTATIONS)->insert([
                'hash' => $hash,
                'language' => $payload['language'],
                'payload' => $this->canonical($payload),
                'dateCreated' => $now,
                'dateUpdated' => $now,
                'uid' => (string) str()->uuid(),
            ]);
        } catch (QueryException $e) {

            return $this->findId($hash) ?? throw $e;
        }

        return $this->findId($hash) ?? throw new RuntimeException('The presentation could not be stored.');
    }

    private function findId(string $hash): ?int
    {
        $id = DB::table(Table::PRESENTATIONS)->where('hash', $hash)->value('id');

        return $id === null ? null : (int) $id;
    }
}
