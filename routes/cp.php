<?php

use CraftCms\Cms\Http\Middleware\RequireAdmin;
use CraftCms\Cms\Http\Middleware\RequireAdminChanges;
use CraftCms\Cms\Support\Url;
use Illuminate\Support\Facades\Route;
use QuebecStudioMods\ConsentKit\CraftCms\Controllers\RegistryController;
use QuebecStudioMods\ConsentKit\CraftCms\Controllers\SettingsController;

// Readable by every admin, read-only when admin changes are off; saving needs them on.
// Under the plugin's settings URL, so the settings nav keeps it selected. The bare
// URL stays Craft's, which leads here through Plugin::getSettingsResponse().
Route::middleware(RequireAdmin::class)->prefix('settings/plugins/cookie-consent-kit')->group(function () {
    Route::get('{pane}', [SettingsController::class, 'show'])->where('pane', '[a-z]+');
    Route::post('{pane}', [SettingsController::class, 'store'])->where('pane', '[a-z]+')->middleware(RequireAdminChanges::class);
});

// Where the screens lived up to 6.1, for bookmarks and links in notes.
Route::get('cookie-consent-kit/settings/{pane?}', fn (?string $pane = null) => redirect()->to(
    Url::cpUrl('settings/plugins/cookie-consent-kit/' . ($pane ?? 'general'), request()->query()),
    301,
))->where('pane', '[a-z]+');

// Permission-gated in the controller rather than admin-only: producing a proof
// is not the same trust as configuring the site.
Route::prefix('cookie-consent-kit/registry')->group(function () {
    Route::get('/', [RegistryController::class, 'index']);
    Route::get('export', [RegistryController::class, 'export']);
    Route::post('purge', [RegistryController::class, 'purge']);
    Route::get('{id}', [RegistryController::class, 'detail'])->where('id', '[0-9]+');
});
