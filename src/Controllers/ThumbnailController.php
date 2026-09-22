<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Controllers;

use CraftCms\Cms\Support\Facades\Path;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Serves YouTube poster images from this site's own domain.
 *
 * The point of the facade is that the visitor's browser never contacts Google
 * before consent. Pointing an `<img>` at `i.ytimg.com` would defeat it exactly
 * as an embedded iframe does: the request carries the IP address, the
 * User-Agent and the referrer. So the server fetches the image instead, once,
 * caches it, and every visitor is served from here.
 *
 * The fetch happens on demand rather than in a queue job: the browser has
 * already been handed the page, and only this one image request waits. A first
 * visitor pays a few hundred milliseconds on it; everyone after is served from
 * disk.
 */
final class ThumbnailController
{
    /**
     * YouTube ids are 11 characters of the URL-safe base64 alphabet. Matching
     * this strictly is what keeps the id from reaching the filesystem or an
     * outbound URL unchecked — the request never chooses a host, only an id.
     */
    private const string ID_PATTERN = '/^[A-Za-z0-9_-]{11}$/';

    /** Ordered by preference: maxres does not exist for every video. */
    private const array QUALITIES = ['maxresdefault', 'hqdefault'];

    private const int MAX_BYTES = 2097152;

    private const int TIMEOUT = 5;

    public function __invoke(Request $request): Response
    {
        $id = (string)$request->query('v', '');

        if (!preg_match(self::ID_PATTERN, $id)) {
            return $this->blank();
        }

        $path = Path::runtime('consent-thumbnails') . DIRECTORY_SEPARATOR . $id . '.jpg';

        if (!is_file($path) && !$this->fetch($id, $path)) {

            return $this->blank(300);
        }

        return response()->file($path, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function fetch(string $id, string $path): bool
    {
        foreach (self::QUALITIES as $quality) {
            try {
                $response = Http::timeout(self::TIMEOUT)
                    ->accept('image/jpeg,image/*')
                    ->get("https://i.ytimg.com/vi/$id/$quality.jpg");
            } catch (Throwable) {
                continue;
            }

            if (!$response->successful()) {
                continue;
            }

            $body = $response->body();

            if (strlen($body) < 1024 || strlen($body) > self::MAX_BYTES) {
                continue;
            }

            try {
                File::ensureDirectoryExists(dirname($path));
                File::put($path, $body);
            } catch (Throwable $e) {
                Log::warning("Could not cache the poster for $id: {$e->getMessage()}");

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * A transparent pixel rather than a 404: the facade layers the poster over
     * a gradient, so an image that resolves to nothing simply lets the
     * gradient show, with no broken-image icon and no JavaScript fallback.
     */
    private function blank(int $maxAge = 31536000): Response
    {

        return new Response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => "public, max-age=$maxAge",
        ]);
    }
}
