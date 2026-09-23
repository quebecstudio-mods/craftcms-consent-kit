<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Utilities;

use Craft;
use craft\base\Utility;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;

/**
 * Deleting records by hand, beside what retention removes on its own.
 */
class RegistryPurge extends Utility
{
    public static function displayName(): string
    {
        return Craft::t('cookie-consent-kit', 'Consent register');
    }

    public static function id(): string
    {
        return 'consent-register';
    }

    public static function icon(): ?string
    {
        return Plugin::getInstance()->getBasePath() . DIRECTORY_SEPARATOR . 'icon-mask.svg';
    }

    public static function contentHtml(): string
    {
        $registry = Plugin::getInstance()->decisions;

        return Craft::$app->getView()->renderTemplate('cookie-consent-kit/registry/_utility', [
            'total' => $registry->total(),
            'oldest' => $registry->oldest(),
            'canPurge' => Craft::$app->getUser()->checkPermission('cookieConsentKit:purgeRegistry'),
        ]);
    }
}
