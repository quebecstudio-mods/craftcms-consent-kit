<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Twig;

use craft\helpers\Html;
use QuebecStudioMods\ConsentKit\Core\CookieTableMarkers;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
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
class ConsentExtension extends AbstractExtension
{
    public function getName(): string
    {
        return 'cookie-consent-kit';
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('withCookieTable', [$this, 'withInventory'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Content with every marker replaced. Content holding no marker is
     * returned untouched, so the filter can be applied to a whole field
     * without knowing whether the author used it.
     *
     * Only content that is already HTML — a CKEditor or Redactor field, whose
     * value extends `Markup` — is passed through as such. Anything else is
     * escaped first: the filter returns HTML, and marking a plain text field
     * safe would strip the escaping Twig would otherwise apply to it.
     */
    public function withInventory(mixed $content, array $options = []): Markup
    {
        $html = $content instanceof Markup
            ? (string)$content
            : Html::encode((string)$content);

        if (!CookieTableMarkers::contains($html)) {
            return new Markup($html, 'UTF-8');
        }

        $consent = Plugin::getInstance()->consent;

        return new Markup(
            CookieTableMarkers::replace($html, static fn (array $opts) => (string)$consent->renderCookieTable($opts), $options),
            'UTF-8'
        );
    }
}
