<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Twig;

use QuebecStudioMods\ConsentKit\CraftCms\Services\Consent;
use QuebecStudioMods\ConsentKit\CraftCms\Views\Html;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * `|withCookieTable`, which swaps a marker written inside content for the
 * declared inventory.
 *
 * A privacy policy is an entry, written by whoever maintains the site, and the
 * table belongs in the middle of that text rather than at a fixed spot in the
 * template. The marker lets the author place it; the template only has to
 * apply the filter once.
 *
 * The name matches `craft.consent.cookieTable()` and the marker itself, so the
 * three read as one feature. Twig filters share a single namespace across every
 * plugin on the install, which is what `with` guards against here.
 */
final class ConsentExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('withCookieTable', [$this, 'withInventory'], ['is_safe' => ['html']]),
        ];
    }

    public function withInventory(mixed $content, array $options = []): Html
    {
        return app(Consent::class)->withCookieTable($content, $options);
    }
}
