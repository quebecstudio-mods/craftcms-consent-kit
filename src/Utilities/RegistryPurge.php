<?php

declare(strict_types=1);

namespace QuebecStudioMods\ConsentKit\CraftCms\Utilities;

use function CraftCms\Cms\t;
use function CraftCms\Cms\template;

use CraftCms\Cms\Utility\Utility;
use Illuminate\Support\Facades\Gate;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Decisions;

/**
 * Deleting records by hand, beside what retention removes on its own.
 */
class RegistryPurge extends Utility
{
    public static function displayName(): string
    {
        return t('Consent purge', category: 'cookie-consent-kit');
    }

    public static function id(): string
    {
        return 'consent-purge';
    }

    public static function icon(): ?string
    {
        return Plugin::getInstance()->getResourcesPath().'/icon-mask.svg';
    }

    public static function contentHtml(): string
    {
        $registry = app(Decisions::class);

        return template('cookie-consent-kit/registry/_utility', [
            'total' => $registry->total(),
            'oldest' => $registry->oldest(),
            'canPurge' => Gate::check('cookieConsentKit:purgeRegistry'),
        ]);
    }
}
