<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use Throwable;
use yii\web\Response;

/**
 * Takes the browser's report of a decision.
 *
 * Anonymous and exempt from CSRF: the token is per session and would have to
 * live in the HTML, which the banner keeps identical for every visitor and
 * cacheable. A bounded payload, an answer checked against the site's
 * inventory and a rate limit stand in its place.
 */
class RecordController extends Controller
{
    private const MAX_BYTES = 4096;

    private const RATE_LIMIT = 20;

    private const RATE_WINDOW = 60;

    protected array|bool|int $allowAnonymous = true;

    public $enableCsrfValidation = false;

    public function actionIndex(): Response
    {
        $this->requirePostRequest();

        $registry = Plugin::getInstance()->decisions;

        if (!$registry->isCollecting() || $this->isOverRate()) {
            return $this->asRaw('')->setStatusCode(202);
        }

        $body = $this->request->getRawBody();

        if ($body === '' || strlen($body) > self::MAX_BYTES) {
            return $this->asRaw('')->setStatusCode(202);
        }

        try {
            $payload = Json::decode($body);
        } catch (Throwable) {
            return $this->asRaw('')->setStatusCode(202);
        }

        if (!is_array($payload)) {
            return $this->asRaw('')->setStatusCode(202);
        }

        $registry->record(
            (int)($payload['site'] ?? 0),
            (string)($payload['action'] ?? ''),
            (string)($payload['origin'] ?? ''),
            is_array($payload['categories'] ?? null) ? $payload['categories'] : [],
        );

        return $this->asRaw('')->setStatusCode(202);
    }

    private function isOverRate(): bool
    {
        $ip = $this->request->getUserIP();

        if (!$ip) {
            return false;
        }

        $cache = Craft::$app->getCache();
        $key = 'cookie-consent-kit:registry:' . sha1($ip);
        $count = (int)$cache->get($key);

        if ($count >= self::RATE_LIMIT) {
            return true;
        }

        $cache->set($key, $count + 1, self::RATE_WINDOW);

        return false;
    }
}
