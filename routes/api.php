<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChapterController;
use App\Http\Controllers\Api\V1\LibraryController;
use App\Http\Controllers\Api\V1\MangaController;
use App\Http\Controllers\Api\V1\ExternalSourceController;
use App\Http\Controllers\Api\V1\Admin\CookieController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/auth/oauth/{provider}/redirect', [AuthController::class, 'oauthRedirect']);
    Route::get('/auth/oauth/{provider}/callback', [AuthController::class, 'oauthCallback']);

    // Protected routes
    Route::middleware('auth:api')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Manga
        Route::apiResource('manga', MangaController::class);
        Route::get('manga/{manga}/versions', [MangaController::class, 'versions']);
        Route::post('manga/{manga}/versions', [MangaController::class, 'linkVersion']);
        Route::delete('manga/{manga}/versions/{versionId}', [MangaController::class, 'unlinkVersion']);
        Route::get('admin/merge/suggestions', [MangaController::class, 'mergeSuggestions']);

        // Chapters
        Route::get('chapters/{chapter}/pages', [ChapterController::class, 'pages']);
        Route::get('chapters/{chapter}/page/{page}', [ChapterController::class, 'page']);
        Route::post('chapters/{chapter}/download', [ChapterController::class, 'downloadFromSource']);

        // Libraries
        Route::apiResource('libraries', LibraryController::class);
        Route::get('libraries/{library}/users', [LibraryController::class, 'users']);
        Route::post('libraries/{library}/users/{user}/assign-role', [LibraryController::class, 'assignRole']);
        Route::delete('libraries/{library}/users/{user}/remove-role/{role}', [LibraryController::class, 'removeRole']);

        // External Sources
        Route::get('sources/{source}/search', [ExternalSourceController::class, 'search']);
        Route::get('sources/{source}/{sourceId}', [ExternalSourceController::class, 'getDetails']);
        Route::post('sources/{source}/{sourceId}/download', [ExternalSourceController::class, 'download']);

        // Cookie Management
        Route::apiResource('cookies', CookieController::class)->except(['show']);
        Route::post('cookies/import', [CookieController::class, 'importFromBrowser']);
    });
});
