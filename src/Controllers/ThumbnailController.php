<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Controllers;

use Craft;
use craft\helpers\FileHelper;
use craft\web\Controller;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use Throwable;
use yii\web\Response;

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
class ThumbnailController extends Controller
{
    protected array|bool|int $allowAnonymous = true;

    /**
     * YouTube ids are 11 characters of the URL-safe base64 alphabet. Matching
     * this strictly is what keeps the id from reaching the filesystem or an
     * outbound URL unchecked — the request never chooses a host, only an id.
     */
    private const ID_PATTERN = '/^[A-Za-z0-9_-]{11}$/';

    /** Ordered by preference: maxres does not exist for every video. */
    private const QUALITIES = ['maxresdefault', 'hqdefault'];

    private const MAX_BYTES = 2097152;

    private const TIMEOUT = 5;

    public function actionIndex(): Response
    {
        $id = (string)$this->request->getParam('v', '');

        if (!preg_match(self::ID_PATTERN, $id)) {
            return $this->blank();
        }

        $path = $this->cachePath($id);

        if (!is_file($path) && !$this->fetch($id, $path)) {

            return $this->blank(300);
        }

        $this->response->getHeaders()
            ->set('Cache-Control', 'public, max-age=31536000, immutable')
            ->set('Content-Type', 'image/jpeg');

        return $this->response->sendFile($path, $id . '.jpg', [
            'inline' => true,
            'mimeType' => 'image/jpeg',
        ]);
    }

    private function fetch(string $id, string $path): bool
    {
        $client = Craft::createGuzzleClient(['timeout' => self::TIMEOUT]);

        foreach (self::QUALITIES as $quality) {
            try {
                $response = $client->get("https://i.ytimg.com/vi/$id/$quality.jpg", [
                    'headers' => ['Accept' => 'image/jpeg,image/*'],
                ]);
            } catch (Throwable) {

                continue;
            }

            $body = (string)$response->getBody();

            if (strlen($body) < 1024 || strlen($body) > self::MAX_BYTES) {
                continue;
            }

            try {
                FileHelper::writeToFile($path, $body);
            } catch (Throwable $e) {
                Craft::warning("Could not cache the poster for $id: {$e->getMessage()}", __METHOD__);
                return false;
            }

            return true;
        }

        return false;
    }

    private function cachePath(string $id): string
    {
        return Plugin::getInstance()->consent->thumbnailCacheDir() . DIRECTORY_SEPARATOR . $id . '.jpg';
    }

    /**
     * A transparent pixel rather than a 404: the facade layers the poster over
     * a gradient, so an image that resolves to nothing simply lets the
     * gradient show, with no broken-image icon and no JavaScript fallback.
     */
    private function blank(int $maxAge = 31536000): Response
    {
        $this->response->getHeaders()
            ->set('Cache-Control', "public, max-age=$maxAge");

        return $this->response->sendContentAsFile(
            base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'),
            'blank.gif',
            ['inline' => true, 'mimeType' => 'image/gif'],
        );
    }
}
