<?php

use CraftCms\Cms\Http\Middleware\RequireAdmin;
use CraftCms\Cms\Http\Middleware\RequireAdminChanges;
use Illuminate\Support\Facades\Route;
use QuebecStudioMods\ConsentKit\CraftCms\Controllers\RegistryController;
use QuebecStudioMods\ConsentKit\CraftCms\Controllers\SettingsController;

// Readable by every admin, read-only when admin changes are off; saving needs them on.
Route::middleware(RequireAdmin::class)->prefix('cookie-consent-kit/settings')->group(function () {
    Route::get('{pane?}', [SettingsController::class, 'show'])->where('pane', '[a-z]+');
    Route::post('{pane}', [SettingsController::class, 'store'])->where('pane', '[a-z]+')->middleware(RequireAdminChanges::class);
});

// Permission-gated in the controller rather than admin-only: producing a proof
// is not the same trust as configuring the site.
Route::prefix('cookie-consent-kit/registry')->group(function () {
    Route::get('/', [RegistryController::class, 'index']);
    Route::get('export', [RegistryController::class, 'export']);
    Route::post('purge', [RegistryController::class, 'purge']);
    Route::get('{id}', [RegistryController::class, 'detail'])->where('id', '[0-9]+');
});
