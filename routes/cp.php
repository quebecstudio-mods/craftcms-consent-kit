<?php

use CraftCms\Cms\Http\Middleware\RequireAdmin;
use CraftCms\Cms\Http\Middleware\RequireAdminChanges;
use Illuminate\Support\Facades\Route;
use QuebecStudioMods\ConsentKit\CraftCms\Controllers\SettingsController;

// Readable by every admin, read-only when admin changes are off; saving needs them on.
Route::middleware(RequireAdmin::class)->prefix('cookie-consent-kit/settings')->group(function () {
    Route::get('{pane?}', [SettingsController::class, 'show'])->where('pane', '[a-z]+');
    Route::post('{pane}', [SettingsController::class, 'store'])->where('pane', '[a-z]+')->middleware(RequireAdminChanges::class);
});
