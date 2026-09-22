<?php

use Illuminate\Support\Facades\Route;
use QuebecStudioMods\ConsentKit\CraftCms\Controllers\ThumbnailController;

// Mounted by Craft under `actions/cookie-consent-kit/`, for site and control
// panel requests alike.
Route::get('thumbnail', ThumbnailController::class);
