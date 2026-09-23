<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Controllers;

use CraftCms\Cms\Config\GeneralConfig;
use CraftCms\Cms\Cp\Data\NavItem;
use CraftCms\Cms\Form\Enums\ControlMode;
use CraftCms\Cms\Form\FormContext;
use CraftCms\Cms\Form\FormResolver;
use CraftCms\Cms\Http\RespondsWithFlash;
use CraftCms\Cms\Http\Responses\CpScreenResponse;
use CraftCms\Cms\Site\Data\Site;
use CraftCms\Cms\Support\Facades\Sites;
use CraftCms\Cms\Support\Url;

use function CraftCms\Cms\t;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Consent;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Decisions;
use QuebecStudioMods\ConsentKit\CraftCms\Services\SettingsStore;
use QuebecStudioMods\ConsentKit\CraftCms\Settings\SettingsForms;
use Symfony\Component\HttpFoundation\Response;

/**
 * The plugin's own settings screens, one per pane. Not Craft's standard
 * plugin settings page: that one saves through savePluginSettings(), which
 * writes config-file values into the project config.
 */
final class SettingsController
{
    use RespondsWithFlash;

    public function __construct(
        private readonly SettingsStore $store,
        private readonly GeneralConfig $generalConfig,
        private readonly FormResolver $formResolver,
    ) {
    }

    public function show(Request $request, ?string $pane = null): CpScreenResponse|RedirectResponse
    {
        $panes = SettingsForms::panes();
        $site = $this->editedSite($request);

        if ($pane === null || !isset($panes[$pane])) {
            return redirect($this->paneUrl(array_key_first($panes), $site));
        }

        $settings = Plugin::getInstance()->getSettings();
        $readOnly = !$this->generalConfig->allowAdminChanges;
        $forms = new SettingsForms(
            $settings,
            $this->store->overrides(),
            $readOnly ? ControlMode::ReadOnly : ControlMode::Editable,
            app(Consent::class)->resolver($site->id),
            $site,
            Sites::getAllSites()->values()->all(),
        );

        $copyFrom = in_array($pane, SettingsForms::copyablePanes(), true) && $request->query('copyFrom')
            ? Sites::getSiteByHandle((string)$request->query('copyFrom'))
            : null;

        return new CpScreenResponse()
            ->title($panes[$pane])
            ->crumbs([
                ['label' => t('Settings'), 'href' => Url::cpUrl('settings')],
                ['label' => t('Plugins'), 'href' => Url::cpUrl('settings/plugins')],
                ['label' => Plugin::getInstance()->name ?? Plugin::NAME, 'href' => $this->paneUrl(array_key_first($panes), $site)],
                ['label' => $panes[$pane]],
            ])
            ->subnav($this->subnav($pane, $site))
            ->inertiaPage('Form', [
                'readOnly' => $readOnly,
                'form' => $this->formResolver->resolve($forms->form($pane), new FormContext(
                    namespace: 'settings',
                    values: $forms->values($copyFrom ? app(Consent::class)->resolver($copyFrom->id) : null),
                )),
                'submit' => [
                    'method' => 'post',
                    'url' => $this->paneUrl($pane, $site),
                ],
            ]);
    }

    public function store(Request $request, string $pane): Response
    {
        abort_unless(isset(SettingsForms::panes()[$pane]), 404);

        $posted = $request->input('settings', []);
        $posted = is_array($posted) ? $posted : [];

        if (!empty($posted['registry']) && !app(Decisions::class)->canEnable()) {
            $posted['registry'] = false;
        }

        $site = in_array($pane, SettingsForms::perSitePanes(), true) ? $this->editedSite($request) : null;
        $model = $this->store->save($posted, $site);

        if ($model->errors()->isNotEmpty()) {
            throw ValidationException::withMessages(collect($model->errors()->getMessages())
                ->mapWithKeys(fn (array $messages, string $key) => ["settings.$key" => $messages])
                ->all());
        }

        return $this->asSuccess(t('Plugin settings saved.'));
    }

    /**
     * `NavItem` carries a numeric badge only, so a Pro pane says so in its
     * label rather than through the control panel's legacy styling.
     *
     * @return list<NavItem>
     */
    private function subnav(string $current, Site $site): array
    {
        $pro = SettingsForms::proPanes();

        return collect(SettingsForms::panes())
            ->map(fn (string $label, string $pane) => new NavItem()
                ->label(in_array($pane, $pro, true)
                    ? $label.' · '.t('Pro', category: 'cookie-consent-kit')
                    : $label)
                ->url($this->paneUrl($pane, $site))
                ->selected($pane === $current))
            ->values()
            ->all();
    }

    /** The site a per-site pane edits: `?site=<handle>`, the primary site otherwise. */
    private function editedSite(Request $request): Site
    {
        $handle = (string)$request->query('site', '');

        return ($handle !== '' ? Sites::getSiteByHandle($handle) : null) ?? Sites::getPrimarySite();
    }

    /** The site travels with every link, so moving between panes keeps it. */
    private function paneUrl(string $pane, Site $site): string
    {
        $params = Sites::getAllSites()->count() > 1 ? ['site' => $site->handle] : null;

        return Url::cpUrl("cookie-consent-kit/settings/$pane", $params);
    }
}
