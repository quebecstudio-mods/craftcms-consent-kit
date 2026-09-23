<?php

declare(strict_types=1);

namespace QuebecStudioMods\ConsentKit\CraftCms\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Decisions;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Takes the browser's report of a decision.
 *
 * Anonymous and exempt from CSRF: the token is per session and would have to
 * live in the HTML, which the banner keeps identical for every visitor and
 * cacheable. A bounded payload, an answer checked against the site's
 * inventory and a rate limit stand in its place.
 */
final readonly class RecordController
{
    private const int MAX_BYTES = 4096;

    private const int RATE_LIMIT = 20;

    private const int RATE_WINDOW = 60;

    public function __construct(private Decisions $decisions)
    {
    }

    public function __invoke(Request $request): Response
    {

        $accepted = response('', 202);

        if (!$this->decisions->isCollecting() || $this->isOverRate($request)) {
            return $accepted;
        }

        $body = $request->getContent();

        if ($body === '' || strlen($body) > self::MAX_BYTES) {
            return $accepted;
        }

        try {
            $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return $accepted;
        }

        if (!is_array($payload)) {
            return $accepted;
        }

        $this->decisions->record(
            (int) ($payload['site'] ?? 0),
            (string) ($payload['action'] ?? ''),
            (string) ($payload['origin'] ?? ''),
            is_array($payload['categories'] ?? null) ? $payload['categories'] : [],
        );

        return $accepted;
    }

    private function isOverRate(Request $request): bool
    {
        $ip = $request->ip();

        if (!$ip) {
            return false;
        }

        $key = 'cookie-consent-kit:registry:'.sha1($ip);
        $count = (int) Cache::get($key, 0);

        if ($count >= self::RATE_LIMIT) {
            return true;
        }

        Cache::put($key, $count + 1, self::RATE_WINDOW);

        return false;
    }
}
