<?php

/**
 * English source strings, listed so every translatable string has one place
 * to be found. Keys and values are identical by design.
 */

return [
    'Cookies' => 'Cookies',
    'General' => 'General',
    'Inventory table' => 'Inventory table',
    'The cookie table can be shown in a page of the site — the privacy policy, most of the time. It has no style of its own: it takes on the style of the page around it.'
        => 'The cookie table can be shown in a page of the site — the privacy policy, most of the time. It has no style of its own: it takes on the style of the page around it.',
    'From a template:' => 'From a template:',
    'From content, once the field goes through the filter:' => 'From content, once the field goes through the filter:',
    'CSS framework' => 'CSS framework',
    'The shipped sets match one version of each framework. Custom writes your own.'
        => 'The shipped sets match one version of each framework. Custom writes your own.',
    'Custom' => 'Custom',
    'Wrapper' => 'Wrapper',
    'Category title' => 'Category title',
    'Category description' => 'Category description',
    'Table' => 'Table',
    'Table header' => 'Table header',
    'Table body' => 'Table body',
    'Row' => 'Row',
    'Header cell' => 'Header cell',
    'Cell' => 'Cell',
    'Handle' => 'Handle',
    'handle' => 'handle',
    'Add a category' => 'Add a category',

    'A category with no declared cookie stays hidden, so adding one costs nothing until it is used. Its handle is permanent: the consent cookie stores it, and renaming it would strand every consent already given.'
        => 'A category with no declared cookie stays hidden, so adding one costs nothing until it is used. Its handle is permanent: the consent cookie stores it, and renaming it would strand every consent already given.',

    'No category' => 'No category',
    'Label' => 'Label',
    'Description' => 'Description',
    'Shown under the category in the manage panel.' => 'Shown under the category in the manage panel.',
    '— always on' => '— always on',
    'Wording is stored per site. Only the selected site is edited here; the others are preserved when you save.'
        => 'Wording is stored per site. Only the selected site is edited here; the others are preserved when you save.',
    'A handle identifies a cookie across every site, and survives a change of technical name. Leave it empty and one is derived from the name.'
        => 'A handle identifies a cookie across every site, and survives a change of technical name. Leave it empty and one is derived from the name.',

    'Skip the banner on a Global Privacy Control refusal' => 'Skip the banner on a Global Privacy Control refusal',
    'Some browsers send a signal meaning “I refuse optional cookies”. That refusal is always honoured: optional categories start off, and nothing is set before consent. This setting only decides whether the banner still asks. Turn it off to keep telling every visitor what the site uses. With the banner skipped, a visitor changes their mind from the reopen tab — so keep that tab on, or provide your own entry point.'
        => 'Some browsers send a signal meaning “I refuse optional cookies”. That refusal is always honoured: optional categories start off, and nothing is set before consent. This setting only decides whether the banner still asks. Turn it off to keep telling every visitor what the site uses. With the banner skipped, a visitor changes their mind from the reopen tab — so keep that tab on, or provide your own entry point.',
    'The banner is skipped for these visitors and the reopen tab is off, so they have no way to accept unless the site provides its own entry point.'
        => 'The banner is skipped for these visitors and the reopen tab is off, so they have no way to accept unless the site provides its own entry point.',

    'Category that lifts the facade' => 'Category that lifts the facade',
    'A visitor who accepted this category gets the video loaded outright, without clicking. Left on “No category”, the facade always applies — which is the safer answer: consent for a category is broader than consent for one video, and Law 25 asks for specific consent. Withdrawing consent restores the facade on the next page load; a player already on screen stays.'
        => 'A visitor who accepted this category gets the video loaded outright, without clicking. Left on “No category”, the facade always applies — which is the safer answer: consent for a category is broader than consent for one video, and Law 25 asks for specific consent. Withdrawing consent restores the facade on the next page load; a player already on screen stays.',
    'Measurement' => 'Measurement',
    'Which category a visitor has to accept before Google Consent Mode and Matomo are granted. A site that measures nothing leaves both on “None”.'
        => 'Which category a visitor has to accept before Google Consent Mode and Matomo are granted. A site that measures nothing leaves both on “None”.',
    'Analytics category' => 'Analytics category',
    'Drives Matomo and Google analytics_storage.' => 'Drives Matomo and Google analytics_storage.',
    'Marketing category' => 'Marketing category',
    'Drives Google ad_storage, ad_user_data and ad_personalization.'
        => 'Drives Google ad_storage, ad_user_data and ad_personalization.',

    'Cookie inventory' => 'Cookie inventory',
    'Consent cookie' => 'Consent cookie',
    'Privacy policy' => 'Privacy policy',
    'Appearance' => 'Appearance',
    'Behaviour' => 'Behaviour',

    'Plugin name' => 'Plugin name',
    'Shown in the control panel. Leave it empty to use the plugin name.'
        => 'Shown in the control panel. Leave it empty to use the plugin name.',
    'Cookie name' => 'Cookie name',
    'Name of the cookie that remembers the visitor’s choice. Renaming it invalidates existing consents — bump the policy version at the same time.'
        => 'Name of the cookie that remembers the visitor’s choice. Renaming it invalidates existing consents — bump the policy version at the same time.',
    'Lifetime' => 'Lifetime',
    'In seconds. 15,552,000 is 180 days.' => 'In seconds. 15,552,000 is 180 days.',
    'Policy version' => 'Policy version',
    'Bump this when a cookie appears in a non-necessary category, a category is added, or a purpose changes. Visitors will then be asked again.'
        => 'Bump this when a cookie appears in a non-necessary category, a category is added, or a purpose changes. Visitors will then be asked again.',
    'Privacy policy page' => 'Privacy policy page',
    'Link to' => 'Link to',
    'A page on this site' => 'A page on this site',
    'A custom URL' => 'A custom URL',
    'This choice is made per site.' => 'This choice is made per site.',
    'The link follows the slug if the page is renamed.' => 'The link follows the slug if the page is renamed.',
    'For a page outside Craft, or an environment variable.' => 'For a page outside Craft, or an environment variable.',
    'Choose a page' => 'Choose a page',
    'Privacy policy URL' => 'Privacy policy URL',
    'Fallback language' => 'Fallback language',

    'Colour scheme' => 'Colour scheme',
    '“Auto” follows the visitor’s system preference. The dark scheme uses the palette set through --qsm-ck-dark-*.'
        => '“Auto” follows the visitor’s system preference. The dark scheme uses the palette set through --qsm-ck-dark-*.',
    'Auto (recommended)' => 'Auto (recommended)',
    'Light' => 'Light',
    'Dark' => 'Dark',
    'Panel backdrop' => 'Panel backdrop',
    'Effect applied behind the “Manage” panel. Blur signals the modality without hiding the page.'
        => 'Effect applied behind the “Manage” panel. Blur signals the modality without hiding the page.',
    'Blur (recommended)' => 'Blur (recommended)',
    'Dim' => 'Dim',
    'None' => 'None',

    'Display mode' => 'Display mode',
    'Full width along the bottom, or a box: floating in the middle, or in a bottom corner. On a narrow screen every mode is full width.'
        => 'Full width along the bottom, or a box: floating in the middle, or in a bottom corner. On a narrow screen every mode is full width.',
    'Full width' => 'Full width',
    'Floating box' => 'Floating box',
    'Bottom left corner' => 'Bottom left corner',
    'Bottom right corner' => 'Bottom right corner',
    'Reopen tab' => 'Reopen tab',
    'Reopen tab position' => 'Reopen tab position',
    'Bottom edge of the screen, on this side. “Auto” follows the display mode: on the right for a bottom right corner, on the left otherwise.'
        => 'Bottom edge of the screen, on this side. “Auto” follows the display mode: on the right for a bottom right corner, on the left otherwise.',
    'Auto' => 'Auto',
    'Left' => 'Left',
    'Right' => 'Right',
    'Small tab shown once the visitor has decided, so the banner can be reopened. Required for compliance — withdrawal must be as easy as consent. Turn it off only if the site provides its own entry point calling window.qsmConsentKit.open().'
        => 'Small tab shown once the visitor has decided, so the banner can be reopened. Required for compliance — withdrawal must be as easy as consent. Turn it off only if the site provides its own entry point calling window.qsmConsentKit.open().',
    'Automatic injection' => 'Automatic injection',
    'Places the banner as the first child of <body>, without touching any template. Turn this off only if the site needs to position it itself. The <head> bootstrap always stays automatic.'
        => 'Places the banner as the first child of <body>, without touching any template. Turn this off only if the site needs to position it itself. The <head> bootstrap always stays automatic.',
    'Template folder' => 'Template folder',
    'A template placed in this folder of the site overrides the plugin’s own. For example resources/views/_consent/banner.twig.'
        => 'A template placed in this folder of the site overrides the plugin’s own. For example resources/views/_consent/banner.twig.',
    'This inventory is a compliance record: it must reflect what the site actually sets. A category with no declared cookie is not shown in the banner.'
        => 'This inventory is a compliance record: it must reflect what the site actually sets. A category with no declared cookie is not shown in the banner.',

    'Category' => 'Category',
    'Name' => 'Name',
    'Set by' => 'Set by',
    'This site' => 'This site',
    'Purpose' => 'Purpose',
    'Retention' => 'Retention',

    'Shown' => 'Shown',
    'always on' => 'always on',
    'Hidden — no cookie declared' => 'Hidden — no cookie declared',

    'Video' => 'Video',
    'The inventory is set in the config file and cannot be edited here.' => 'The inventory is set in the config file and cannot be edited here.',
    'Delete this category' => 'Delete this category',
    'Removed with its cookies when you save. Visitors who accepted it keep that in their consent cookie until the policy version is bumped, and any tag marked with its handle stops being activated.'
        => 'Removed with its cookies when you save. Visitors who accepted it keep that in their consent cookie until the policy version is bumped, and any tag marked with its handle stops being activated.',
    'Site:' => 'Site:',
    'Copy from:' => 'Copy from:',
    'Set in the config file, which takes precedence.' => 'Set in the config file, which takes precedence.',
    'Used when the current locale has no wording. The list holds the languages the plugin ships with, plus any the site adds in lang/vendor/cookie-consent-kit.'
        => 'Used when the current locale has no wording. The list holds the languages the plugin ships with, plus any the site adds in lang/vendor/cookie-consent-kit.',
    'YouTube videos currently load without consent.' => 'YouTube videos currently load without consent.',
    'YouTube facade' => 'YouTube facade',
    'YouTube videos load only when the visitor clicks, so nothing reaches Google beforehand — the click is the consent, for that video alone. Only YouTube is covered: videos hosted elsewhere are untouched by this setting, and each template is responsible for them. Turning this off embeds YouTube directly, which lets Google set cookies as soon as the page is displayed, without any consent.'
        => 'YouTube videos load only when the visitor clicks, so nothing reaches Google beforehand — the click is the consent, for that video alone. Only YouTube is covered: videos hosted elsewhere are untouched by this setting, and each template is responsible for them. Turning this off embeds YouTube directly, which lets Google set cookies as soon as the page is displayed, without any consent.',
    'YouTube thumbnails' => 'YouTube thumbnails',
    'Show the real thumbnail on the facade. The server fetches it from YouTube once, caches it, and serves it from this domain — the visitor never contacts Google before clicking. Turning this off falls back to a plain gradient.'
        => 'Show the real thumbnail on the facade. The server fetches it from YouTube once, caches it, and serves it from this domain — the visitor never contacts Google before clicking. Turning this off falls back to a plain gradient.',
];
