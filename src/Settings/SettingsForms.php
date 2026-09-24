<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Settings;

use CraftCms\Cms\Cp\SelectOptions;
use CraftCms\Cms\Entry\Elements\Entry;
use CraftCms\Cms\Form\Controls\Choice;
use CraftCms\Cms\Form\Controls\Control;
use CraftCms\Cms\Form\Controls\ElementSelect;
use CraftCms\Cms\Form\Controls\Lightswitch;
use CraftCms\Cms\Form\Controls\Number;
use CraftCms\Cms\Form\Controls\Table;
use CraftCms\Cms\Form\Controls\Text;
use CraftCms\Cms\Form\Controls\Textarea;
use CraftCms\Cms\Form\Enums\ChoicePresentation;
use CraftCms\Cms\Form\Enums\ControlMode;
use CraftCms\Cms\Form\Form;
use CraftCms\Cms\Form\Nodes\Field;
use CraftCms\Cms\Form\Nodes\Heading;
use CraftCms\Cms\Form\Nodes\HiddenField;
use CraftCms\Cms\Form\Nodes\MarkdownContent;
use CraftCms\Cms\Site\Data\Site;
use CraftCms\Cms\Support\Url;

use function CraftCms\Cms\t;

use InvalidArgumentException;
use Locale;
use QuebecStudioMods\ConsentKit\Core\Defaults;
use QuebecStudioMods\ConsentKit\Core\Resolver;
use QuebecStudioMods\ConsentKit\Core\Settings\Catalogue;
use QuebecStudioMods\ConsentKit\CraftCms\Models\Settings;
use QuebecStudioMods\ConsentKit\CraftCms\Plugin;
use QuebecStudioMods\ConsentKit\CraftCms\Services\Decisions;

/**
 * The settings panes, described with Craft 6's Control Panel Form API: the
 * control panel renders them, the plugin ships no template.
 *
 * A setting the config file sets is shown read-only, with a note: the file
 * wins, so editing it here would change nothing.
 */
final class SettingsForms
{
    /**
     * `$site` is the site a per-site pane edits, `$resolver` resolves the
     * settings for it, `$sites` are every site of the install.
     *
     * @param Site[] $sites
     */
    public function __construct(
        private readonly Settings $settings,
        private readonly array $overrides,
        private readonly ControlMode $mode,
        private readonly Resolver $resolver,
        private readonly Site $site,
        private readonly array $sites,
    ) {
    }

    /**
     * The values the form shows. Per-site settings are narrowed to the edited
     * site, keyed the way the form posts them; `$copyFromResolver` prefills
     * the inventory wording with another site's, saved only when the admin
     * saves.
     */
    public function values(?Resolver $copyFromResolver = null): array
    {
        $values = $this->settings->validationData();
        $id = $this->site->id;

        $values['policySource'] = [$id => $this->resolver->policySource()];
        $values['policyEntry'] = [$id => array_filter([$this->resolver->policyEntryId()])];
        $values['policyUrl'] = [$id => $this->rawPerSite($this->settings->policyUrl)];

        $values['categories'] = $this->categoryValues($copyFromResolver ?? $this->resolver);
        $values['addCategory'] = '1';

        $values['registry'] = app(Decisions::class)->isCollecting();

        return ['settings' => $values];
    }

    /**
     * Categories in the shape the cookies pane posts: the site's wording,
     * resolved the way visitors see it, and one table row per cookie.
     *
     * The cookie name stays raw: resolved, `{cookieName}` would be saved as
     * the current name and stop following the Consent cookie setting.
     */
    private function categoryValues(Resolver $wording): array
    {
        $words = array_column($wording->categories(), null, 'handle');
        $raw = $this->settings->categories ?: Settings::defaultCategories();

        return collect($this->resolver->categories())->mapWithKeys(function (array $category) use ($words, $raw) {
            $handle = $category['handle'];
            $source = $words[$handle] ?? $category;
            $sourceCookies = array_column($source['cookies'], null, 'handle');

            return [$handle => [
                'label' => $source['label'],
                'description' => $source['description'],
                'cookies' => array_map(fn (array $cookie) => [
                    'handle' => $cookie['handle'],
                    'name' => (string)($raw[$handle]['cookies'][$cookie['handle']]['name'] ?? $cookie['name']),
                    'provider' => (string)$cookie['provider'],
                    'purpose' => $sourceCookies[$cookie['handle']]['purpose'] ?? $cookie['purpose'],
                    'duration' => $sourceCookies[$cookie['handle']]['duration'] ?? $cookie['duration'],
                ], $category['cookies']),
            ]];
        })->all();
    }

    /**
     * Panes in the order a site is configured: what the install needs first,
     * then what the law requires, then how the banner behaves, reads and looks.
     *
     * @return array<string, string>
     */
    public static function panes(): array
    {
        return [
            'general' => t('General', category: 'cookie-consent-kit'),
            'cookie' => t('Consent cookie', category: 'cookie-consent-kit'),
            'policy' => t('Privacy policy', category: 'cookie-consent-kit'),
            'cookies' => t('Cookie inventory', category: 'cookie-consent-kit'),
            'behaviour' => t('Behaviour', category: 'cookie-consent-kit'),
            'video' => t('Video', category: 'cookie-consent-kit'),
            'appearance' => t('Appearance', category: 'cookie-consent-kit'),
            'registry' => t('Register', category: 'cookie-consent-kit'),
        ];
    }

    /** Panes the Pro edition opens. */
    public static function proPanes(): array
    {
        return ['registry'];
    }

    /** Panes whose settings are made per site. */
    public static function perSitePanes(): array
    {
        return ['policy', 'cookies'];
    }

    /** Per-site panes that can prefill their wording from another site. */
    public static function copyablePanes(): array
    {
        return ['cookies'];
    }

    public function form(string $pane): Form
    {
        return match ($pane) {
            'general' => $this->general(),
            'cookie' => $this->cookie(),
            'policy' => $this->policy(),
            'cookies' => $this->cookies(),
            'behaviour' => $this->behaviour(),
            'video' => $this->video(),
            'appearance' => $this->appearance(),
            'registry' => $this->registry(),
            default => throw new InvalidArgumentException("Unknown pane: $pane"),
        };
    }

    private function policy(): Form
    {
        $id = $this->site->id;
        $perSite = $this->multisite() ? t('This choice is made per site.', category: 'cookie-consent-kit') : null;

        return Form::make([
            ...$this->siteSwitcher('policy'),
            Field::make(
                t('Link to', category: 'cookie-consent-kit'),
                Choice::make("policySource.$id")->presentation(ChoicePresentation::Select)->mode($this->perSiteMode('policySource'))->options([
                    ['label' => t('A page on this site', category: 'cookie-consent-kit'), 'value' => 'entry'],
                    ['label' => t('A custom URL', category: 'cookie-consent-kit'), 'value' => 'url'],
                ]),
            )->tip($perSite)->warning($this->perSiteLockNote('policySource')),
            Field::make(
                t('Privacy policy page', category: 'cookie-consent-kit'),
                ElementSelect::make("policyEntry.$id")
                    ->elementType(Entry::class)
                    ->limit(1)
                    ->criteria(['siteId' => $id])
                    ->selectionLabel(t('Choose a page', category: 'cookie-consent-kit'))
                    ->mode($this->perSiteMode('policyEntry')),
            )->instructions(t('The link follows the slug if the page is renamed.', category: 'cookie-consent-kit'))
                ->warning($this->perSiteLockNote('policyEntry')),
            Field::make(
                t('Privacy policy URL', category: 'cookie-consent-kit'),
                Text::make("policyUrl.$id")
                    ->placeholder(Defaults::POLICY_URL)
                    ->textExpanderTriggers(SelectOptions::getEnvTextExpanderTriggers())
                    ->mode($this->perSiteMode('policyUrl')),
            )->instructions(t('For a page outside Craft, or an environment variable.', category: 'cookie-consent-kit'))
                ->warning($this->perSiteLockNote('policyUrl')),
        ]);
    }

    private function cookies(): Form
    {
        $fields = [
            ...$this->siteSwitcher('cookies'),
            MarkdownContent::make('inventory-status', $this->inventoryStatus()),
        ];

        if (array_key_exists('categories', $this->overrides)) {
            $fields[] = MarkdownContent::make('inventory-locked', '**' . t('The inventory is set in the config file and cannot be edited here.', category: 'cookie-consent-kit') . '**');
        } else {
            $fields[] = MarkdownContent::make('inventory-tip', implode(' ', [
                t('Wording is stored per site. Only the selected site is edited here; the others are preserved when you save.', category: 'cookie-consent-kit'),
                t('A handle identifies a cookie across every site, and survives a change of technical name. Leave it empty and one is derived from the name.', category: 'cookie-consent-kit'),
            ]));
            array_push($fields, ...$this->copyFromLinks('cookies'));

            foreach ($this->resolver->categories() as $category) {
                array_push($fields, ...$this->categoryNodes($category));
            }

            if ($this->mode === ControlMode::Editable) {
                array_push($fields, ...$this->newCategoryNodes());
            }
        }

        $fields[] = Heading::make('measurement-heading', t('Measurement', category: 'cookie-consent-kit'));
        $fields[] = MarkdownContent::make('measurement-tip', t('Which category a visitor has to accept before Google Consent Mode and Matomo are granted. A site that measures nothing leaves both on “None”.', category: 'cookie-consent-kit'));
        $fields[] = $this->catalogued(
            'analyticsCategory',
            Choice::make('analyticsCategory')->presentation(ChoicePresentation::Select)->options($this->categoryOptions(t('None', category: 'cookie-consent-kit'))),
        );
        $fields[] = $this->catalogued(
            'marketingCategory',
            Choice::make('marketingCategory')->presentation(ChoicePresentation::Select)->options($this->categoryOptions(t('None', category: 'cookie-consent-kit'))),
        );

        return Form::make($fields);
    }

    /** What the banner shows for each category, as a compliance check. */
    private function inventoryStatus(): string
    {
        $lines = array_map(function (array $category): string {
            $status = $category['visible']
                ? t('Shown', category: 'cookie-consent-kit') . ($category['required'] ? ' — ' . t('always on', category: 'cookie-consent-kit') : '')
                : '*' . t('Hidden — no cookie declared', category: 'cookie-consent-kit') . '*';

            return '- **' . ($category['label'] ?: $category['handle']) . '** `' . $category['handle'] . '` · '
                . t('Cookies', category: 'cookie-consent-kit') . ' : ' . count($category['cookies']) . ' · ' . $status;
        }, $this->resolver->categories());

        return t('This inventory is a compliance record: it must reflect what the site actually sets. A category with no declared cookie is not shown in the banner.', category: 'cookie-consent-kit')
            . "\n\n" . implode("\n", $lines);
    }

    private function categoryNodes(array $category): array
    {
        $handle = $category['handle'];
        $title = ($category['label'] ?: $handle) . " ($handle)";

        if ($category['required']) {
            $title .= ' ' . t('— always on', category: 'cookie-consent-kit');
        }

        $nodes = [
            Heading::make("category-$handle-heading", $title),
            Field::make(t('Label', category: 'cookie-consent-kit'), Text::make("categories.$handle.label")->mode($this->mode)),
            Field::make(t('Description', category: 'cookie-consent-kit'), Textarea::make("categories.$handle.description")->rows(2)->mode($this->mode))
                ->instructions(t('Shown under the category in the manage panel.', category: 'cookie-consent-kit')),
            Field::make(t('Cookies', category: 'cookie-consent-kit'), Table::make("categories.$handle.cookies")
                ->columns($this->cookieColumns())
                ->allowAdd()
                ->allowDelete()
                ->allowReorder()
                ->mode($this->mode)),
        ];

        if (!$category['required'] && $this->mode === ControlMode::Editable) {
            $nodes[] = Field::make(t('Delete this category', category: 'cookie-consent-kit'), Lightswitch::make("deleteCategory.$handle"))
                ->instructions(t('Removed with its cookies when you save. Visitors who accepted it keep that in their consent cookie until the policy version is bumped, and any tag marked with its handle stops being activated.', category: 'cookie-consent-kit'));
        }

        return $nodes;
    }

    private function newCategoryNodes(): array
    {
        return [
            Heading::make('new-category-heading', t('Add a category', category: 'cookie-consent-kit')),
            MarkdownContent::make('new-category-tip', t('A category with no declared cookie stays hidden, so adding one costs nothing until it is used. Its handle is permanent: the consent cookie stores it, and renaming it would strand every consent already given.', category: 'cookie-consent-kit')),
            HiddenField::make('addCategory'),
            Field::make(t('Label', category: 'cookie-consent-kit'), Text::make('newCategory.label'))->width(50),
            Field::make(t('Handle', category: 'cookie-consent-kit'), Text::make('newCategory.handle')->monospace())->width(50),
        ];
    }

    private function cookieColumns(): array
    {
        return [
            'handle' => ['type' => 'singleline', 'heading' => t('Handle', category: 'cookie-consent-kit'), 'code' => true, 'thin' => true],
            'name' => ['type' => 'singleline', 'heading' => t('Name', category: 'cookie-consent-kit'), 'code' => true],
            'provider' => ['type' => 'singleline', 'heading' => t('Set by', category: 'cookie-consent-kit'), 'placeholder' => t('This site', category: 'cookie-consent-kit')],
            'purpose' => ['type' => 'multiline', 'heading' => t('Purpose', category: 'cookie-consent-kit')],
            'duration' => ['type' => 'singleline', 'heading' => t('Retention', category: 'cookie-consent-kit'), 'thin' => true],
        ];
    }

    private function multisite(): bool
    {
        return count($this->sites) > 1;
    }

    /** Links to the same pane for the other sites; nothing on a single-site install. */
    private function siteSwitcher(string $pane): array
    {
        if (!$this->multisite()) {
            return [];
        }

        $links = array_map(
            fn (Site $site) => $site->id === $this->site->id
                ? '**' . $site->getName() . '**'
                : '[' . $site->getName() . '](' . Url::cpUrl("cookie-consent-kit/settings/$pane", ['site' => $site->handle]) . ')',
            $this->sites
        );

        return [MarkdownContent::make('site-switcher', t('Site:', category: 'cookie-consent-kit') . ' ' . implode(' · ', $links))];
    }

    /**
     * Reloads the pane with another site's wording in the fields. Nothing is
     * written until the admin saves, so the copy can be read, edited or
     * abandoned by leaving the page.
     */
    private function copyFromLinks(string $pane): array
    {
        $others = array_filter($this->sites, fn (Site $site) => $site->id !== $this->site->id);

        if ($others === [] || $this->mode !== ControlMode::Editable) {
            return [];
        }

        $links = array_map(
            fn (Site $site) => '[' . $site->getName() . '](' . Url::cpUrl("cookie-consent-kit/settings/$pane", ['site' => $this->site->handle, 'copyFrom' => $site->handle]) . ')',
            $others
        );

        return [MarkdownContent::make('copy-from', t('Copy from:', category: 'cookie-consent-kit') . ' ' . implode(' · ', $links))];
    }

    /**
     * A per-site setting in the config file locks only the sites it covers:
     * a plain value covers all of them, a map only its own keys.
     */
    private function lockedForSite(string $name): bool
    {
        if (!array_key_exists($name, $this->overrides)) {
            return false;
        }

        $value = $this->overrides[$name];

        return !is_array($value) || array_key_exists($this->site->id, $value) || array_key_exists($this->site->handle, $value);
    }

    private function perSiteMode(string $name): ControlMode
    {
        return $this->lockedForSite($name) ? ControlMode::ReadOnly : $this->mode;
    }

    private function perSiteLockNote(string $name): ?string
    {
        return $this->lockedForSite($name)
            ? t('Set in the config file, which takes precedence.', category: 'cookie-consent-kit')
            : null;
    }

    /** A per-site setting as stored, before environment references are resolved. */
    private function rawPerSite(mixed $value): string
    {
        if (is_array($value)) {
            $value = $value[$this->site->id] ?? $value[$this->site->handle] ?? '';
        }

        return (string)$value;
    }

    private function appearance(): Form
    {
        $fields = [
            $this->catalogued(
                'colorScheme',
                Choice::make('colorScheme')->presentation(ChoicePresentation::Select)->options([
                    ['label' => t('Auto (recommended)', category: 'cookie-consent-kit'), 'value' => 'auto'],
                    ['label' => t('Light', category: 'cookie-consent-kit'), 'value' => 'light'],
                    ['label' => t('Dark', category: 'cookie-consent-kit'), 'value' => 'dark'],
                ]),
            ),
            $this->catalogued(
                'backdropStyle',
                Choice::make('backdropStyle')->presentation(ChoicePresentation::Select)->options([
                    ['label' => t('Blur (recommended)', category: 'cookie-consent-kit'), 'value' => 'blur'],
                    ['label' => t('Dim', category: 'cookie-consent-kit'), 'value' => 'dim'],
                    ['label' => t('None', category: 'cookie-consent-kit'), 'value' => 'none'],
                ]),
            ),
            $this->catalogued(
                'displayMode',
                Choice::make('displayMode')->presentation(ChoicePresentation::Select)->options([
                    ['label' => t('Full width', category: 'cookie-consent-kit'), 'value' => 'full'],
                    ['label' => t('Floating box', category: 'cookie-consent-kit'), 'value' => 'floating'],
                    ['label' => t('Bottom left corner', category: 'cookie-consent-kit'), 'value' => 'corner-left'],
                    ['label' => t('Bottom right corner', category: 'cookie-consent-kit'), 'value' => 'corner-right'],
                ]),
            ),
            $this->field(
                'templateRoot',
                t('Template folder', category: 'cookie-consent-kit'),
                t('A template placed in this folder of the site overrides the plugin’s own. For example resources/views/_consent/banner.twig.', category: 'cookie-consent-kit'),
                Text::make('templateRoot'),
            ),
            Heading::make('inventory-heading', t('Inventory table', category: 'cookie-consent-kit')),
            MarkdownContent::make('inventory-tip', implode("\n\n", [
                t('The cookie table can be shown in a page of the site — the privacy policy, most of the time. It has no style of its own: it takes on the style of the page around it.', category: 'cookie-consent-kit'),
                t('From a template:', category: 'cookie-consent-kit') . ' `{{ craft.consent.cookieTable() }}`',
                t('From content, once the field goes through the filter:', category: 'cookie-consent-kit') . ' `[cookie-table]`',
            ])),
            $this->field(
                'inventoryFramework',
                t('CSS framework', category: 'cookie-consent-kit'),
                t('The shipped sets match one version of each framework. Custom writes your own.', category: 'cookie-consent-kit'),
                Choice::make('inventoryFramework')->presentation(ChoicePresentation::Select)->options([
                    ['label' => t('None', category: 'cookie-consent-kit'), 'value' => ''],
                    ...array_map(
                        static fn (string $handle, array $preset) => ['label' => $preset['label'], 'value' => $handle],
                        array_keys(Defaults::inventoryPresets()),
                        Defaults::inventoryPresets()
                    ),
                    ['label' => t('Custom', category: 'cookie-consent-kit'), 'value' => 'custom'],
                ]),
            ),
        ];

        if ($this->settings->inventoryFramework === 'custom') {
            foreach ($this->inventoryElementLabels() as $element => $label) {
                $fields[] = Field::make($label, Text::make("inventoryClasses.$element")->mode($this->modeFor('inventoryClasses')))
                    ->width(50)
                    ->warning($this->lockNote('inventoryClasses'));
            }
        }

        return Form::make($fields);
    }

    private function inventoryElementLabels(): array
    {
        return [
            'wrapper' => t('Wrapper', category: 'cookie-consent-kit') . ' <div>',
            'section' => t('Category', category: 'cookie-consent-kit') . ' <section>',
            'heading' => t('Category title', category: 'cookie-consent-kit') . ' <h2>–<h6>',
            'description' => t('Category description', category: 'cookie-consent-kit') . ' <p>',
            'table' => t('Table', category: 'cookie-consent-kit') . ' <table>',
            'thead' => t('Table header', category: 'cookie-consent-kit') . ' <thead>',
            'tbody' => t('Table body', category: 'cookie-consent-kit') . ' <tbody>',
            'tr' => t('Row', category: 'cookie-consent-kit') . ' <tr>',
            'th' => t('Header cell', category: 'cookie-consent-kit') . ' <th>',
            'td' => t('Cell', category: 'cookie-consent-kit') . ' <td>',
        ];
    }

    private function general(): Form
    {
        return Form::make([
            $this->field(
                'defaultLanguage',
                t('Fallback language', category: 'cookie-consent-kit'),
                t('Used when the current locale has no wording. The list holds the languages the plugin ships with, plus any the site adds in lang/vendor/cookie-consent-kit.', category: 'cookie-consent-kit'),
                Choice::make('defaultLanguage')->presentation(ChoicePresentation::Select)->options($this->languageOptions()),
            )->required(),
        ]);
    }

    private function cookie(): Form
    {
        return Form::make([
            $this->catalogued(
                'cookieName',
                Text::make('cookieName')->textExpanderTriggers(SelectOptions::getEnvTextExpanderTriggers()),
            )->required(),
            $this->catalogued(
                'cookieMaxAge',
                Number::make('cookieMaxAge'),
            ),
            $this->catalogued(
                'version',
                Number::make('version'),
            ),
        ]);
    }

    private function registry(): Form
    {
        $decisions = app(Decisions::class);
        $collecting = $decisions->isCollecting();
        $fields = [];

        if (!$decisions->canEnable()) {
            $fields[] = MarkdownContent::make('registry-edition', implode(' ', [
                '**' . t('Pro', category: 'cookie-consent-kit') . '**',
                t('Keeping a server-side register is part of the Pro edition. Standard collects and honours consent; Pro archives the proof.', category: 'cookie-consent-kit'),
            ]));

            if ($decisions->isSuspended()) {
                $fields[] = MarkdownContent::make(
                    'registry-suspended',
                    t('This install asks for a register in its configuration. Without the Pro edition it stays dormant, and nothing is written. Records already kept remain readable, exportable and purgeable.', category: 'cookie-consent-kit'),
                );
            }
        }

        $fields[] = Field::make(
            t('Record decisions', category: 'cookie-consent-kit'),
            Lightswitch::make('registry')->mode($decisions->canEnable() ? $this->modeFor('registry') : ControlMode::ReadOnly),
        )
            ->instructions(t('Each decision is written down as the browser makes it: the server clock, the site, the categories answered, and a fingerprint of the wording that was on screen. The cookie’s own timestamp lives on the visitor’s device and proves nothing. Off by default — a register is something a site announces in its privacy policy.', category: 'cookie-consent-kit'))
            ->warning($this->lockNote('registry'));

        if ($collecting) {
            $fields[] = $this->cataloguedSwitch(
                'registryUser',
            );

            $fields[] = $this->cataloguedSwitch(
                'registryRequestContext',
            );
        }

        $fields[] = $this->catalogued(
            'registryGrace',
            Number::make('registryGrace'),
        );

        return Form::make($fields);
    }

    private function video(): Form
    {
        $facadeWarning = $this->settings->videoFacade
            ? null
            : t('YouTube videos currently load without consent.', category: 'cookie-consent-kit');

        $fields = [
            $this->cataloguedSwitch(
                'videoFacade',
                $facadeWarning,
            ),
        ];

        if ($this->settings->videoFacade) {
            $fields[] = $this->cataloguedSwitch(
                'videoThumbnails',
            );
            $fields[] = $this->field(
                'videoConsentCategory',
                t('Category that lifts the facade', category: 'cookie-consent-kit'),
                t('A visitor who accepted this category gets the video loaded outright, without clicking. Left on “No category”, the facade always applies — which is the safer answer: consent for a category is broader than consent for one video, and Law 25 asks for specific consent. Withdrawing consent restores the facade on the next page load; a player already on screen stays.', category: 'cookie-consent-kit'),
                Choice::make('videoConsentCategory')->presentation(ChoicePresentation::Select)->options($this->categoryOptions()),
            );
        }

        return Form::make($fields);
    }

    private function behaviour(): Form
    {
        $gpcWarning = $this->settings->gpcHidesBanner && !$this->settings->reopenButton
            ? t('The banner is skipped for these visitors and the reopen tab is off, so they have no way to accept unless the site provides its own entry point.', category: 'cookie-consent-kit')
            : null;

        return Form::make([
            $this->cataloguedSwitch(
                'reopenButton',
            ),
            $this->catalogued(
                'reopenPosition',
                Choice::make('reopenPosition')->presentation(ChoicePresentation::Select)->options([
                    ['label' => t('Auto', category: 'cookie-consent-kit'), 'value' => 'auto'],
                    ['label' => t('Left', category: 'cookie-consent-kit'), 'value' => 'left'],
                    ['label' => t('Right', category: 'cookie-consent-kit'), 'value' => 'right'],
                ]),
            ),
            $this->cataloguedSwitch(
                'gpcHidesBanner',
                $gpcWarning,
            ),
            $this->cataloguedSwitch('autoInject'),
        ]);
    }

    /**
     * A field whose label and help come from the core's catalogue, so the same
     * setting reads the same wherever it is offered. The control stays this
     * screen's: Craft CMS knows the site's languages and categories, which the
     * catalogue cannot.
     */
    private function catalogued(string $name, Control $control, ?string $warning = null): Field
    {
        $field = Catalogue::fields()[$name];

        return $this->field($name, $this->wording($field['label']), $this->wording($field['help'] ?? ''), $control, $warning);
    }

    private function cataloguedSwitch(string $name, ?string $warning = null): Field
    {
        return $this->catalogued($name, Lightswitch::make($name), $warning);
    }

    /**
     * A catalogue string as this screen shows it: `:tag` is the template call,
     * and the backticks that mark code in a Markdown panel are dropped, since
     * Craft CMS renders instructions as plain text.
     */
    private function wording(string $string): string
    {
        $translated = t($string, ['tag' => 'craft.consent.banner()'], category: 'cookie-consent-kit');

        return str_replace('`', '', $translated);
    }

    private function field(string $name, string $label, string $instructions, Control $control, ?string $warning = null): Field
    {
        return Field::make($label, $control->mode($this->modeFor($name)))
            ->instructions($instructions)
            ->warning($this->lockNote($name) ?? $warning);
    }

    /** The languages with a wording file, named in the admin's language. */
    private function languageOptions(): array
    {
        return array_map(static function (string $code): array {
            $name = Locale::getDisplayName($code, app()->getLocale());

            return ['label' => $name && $name !== $code ? "$name ($code)" : $code, 'value' => $code];
        }, $this->resolver->availableLanguages());
    }

    /** “No category” is a real answer: a site measuring nothing has none to point at. */
    private function categoryOptions(?string $emptyLabel = null): array
    {
        return [
            ['label' => $emptyLabel ?? t('No category', category: 'cookie-consent-kit'), 'value' => ''],
            ...array_map(
                static fn (array $category) => ['label' => $category['label'] ?: $category['handle'], 'value' => $category['handle']],
                $this->resolver->categories()
            ),
        ];
    }

    private function modeFor(string $name): ControlMode
    {
        return array_key_exists($name, $this->overrides) ? ControlMode::ReadOnly : $this->mode;
    }

    private function lockNote(string $name): ?string
    {
        return array_key_exists($name, $this->overrides)
            ? t('Set in the config file, which takes precedence.', category: 'cookie-consent-kit')
            : null;
    }
}
