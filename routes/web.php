<?php

use App\Http\Controllers\Admin\ChapterController as AdminChapterController;
use App\Http\Controllers\Admin\MangaController as AdminMangaController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\MangaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ChapterListController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Homepage
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Frontend Manga Routes
|--------------------------------------------------------------------------
*/

Route::prefix('manga')->name('manga.')->group(function () {
    // Manga Listing
    Route::get('/', [MangaController::class, 'index'])->name('index');

    // Manga Detail
    Route::get('/{manga}', [MangaController::class, 'show'])->name('show');

    // Manga Chapters List
    Route::get('/{manga}/chapters', [MangaController::class, 'chapters'])->name('chapters');
});

/*
|--------------------------------------------------------------------------
| Reader Routes
|--------------------------------------------------------------------------
*/

Route::prefix('read')->name('chapter.')->group(function () {
    // Chapter Reader
    Route::get('/{manga}/{chapter}', [ChapterController::class, 'show'])->name('show');

    // Get Pages Info (AJAX)
    Route::get('/{manga}/{chapter}/pages', [ChapterController::class, 'pages'])->name('pages');

    // Serve Individual Page
    Route::get('/{manga}/{chapter}/page/{page}', [ChapterController::class, 'page'])
        ->where('page', '[0-9]+')
        ->name('page');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Admin Manga Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('manga')->name('manga.')->group(function () {
        // Manga CRUD
        Route::get('/', [AdminMangaController::class, 'index'])->name('index');
        Route::get('/create', [AdminMangaController::class, 'create'])->name('create');
        Route::post('/', [AdminMangaController::class, 'store'])->name('store');
        Route::get('/{manga}', [AdminMangaController::class, 'show'])->name('show');
        Route::get('/{manga}/edit', [AdminMangaController::class, 'edit'])->name('edit');
        Route::put('/{manga}', [AdminMangaController::class, 'update'])->name('update');
        Route::delete('/{manga}', [AdminMangaController::class, 'destroy'])->name('destroy');

        // Manga Actions
        Route::post('/{manga}/toggle-active', [AdminMangaController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/{manga}/toggle-featured', [AdminMangaController::class, 'toggleFeatured'])->name('toggle-featured');

        // Soft Delete Management
        Route::post('/{id}/restore', [AdminMangaController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [AdminMangaController::class, 'forceDelete'])->name('force-delete');

        /*
        |--------------------------------------------------------------------------
        | Admin Chapter Routes (Nested under Manga)
        |--------------------------------------------------------------------------
        */

        Route::prefix('{manga}/chapters')->name('chapters.')->group(function () {
            // Chapter CRUD
            Route::get('/', [AdminChapterController::class, 'index'])->name('index');
            Route::get('/create', [AdminChapterController::class, 'create'])->name('create');
            Route::post('/', [AdminChapterController::class, 'store'])->name('store');
            Route::get('/{chapter}', [AdminChapterController::class, 'show'])->name('show');
            Route::get('/{chapter}/edit', [AdminChapterController::class, 'edit'])->name('edit');
            Route::put('/{chapter}', [AdminChapterController::class, 'update'])->name('update');
            Route::delete('/{chapter}', [AdminChapterController::class, 'destroy'])->name('destroy');

            // Chapter Actions
            Route::post('/{chapter}/toggle-active', [AdminChapterController::class, 'toggleActive'])->name('toggle-active');

            // Bulk Upload
            Route::post('/bulk-upload', [AdminChapterController::class, 'bulkUpload'])->name('bulk-upload');

            // Update Sort Order
            Route::post('/update-order', [AdminChapterController::class, 'updateOrder'])->name('update-order');

            // Soft Delete Management
            Route::post('/{chapter}/restore', [AdminChapterController::class, 'restore'])->name('restore');
            Route::delete('/{chapter}/force-delete', [AdminChapterController::class, 'forceDelete'])->name('force-delete');
        });
    });
});
