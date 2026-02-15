<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ChapterController extends Controller
{
    /**
     * Display the chapter reader.
     */
    public function show(Manga $manga, Chapter $chapter): View
    {
        abort_if(!$manga->is_active, 404);
        abort_if(!$chapter->is_active, 404);
        abort_if($chapter->manga_id !== $manga->id, 404);

        $chapter->load('manga.translations');

        // Increment views
        $chapter->incrementViews();
        $manga->incrementViews();

        // Get previous and next chapters
        $previousChapter = $chapter->previous();
        $nextChapter = $chapter->next();

        // Extract pages info
        $pages = $chapter->extractPages();
        $pageCount = count($pages);

        return view('reader.show', [
            'manga' => $manga,
            'chapter' => $chapter,
            'pageCount' => $pageCount,
            'previousChapter' => $previousChapter,
            'nextChapter' => $nextChapter,
        ]);
    }

    /**
     * Serve a specific page from the chapter CBZ.
     */
    public function page(Manga $manga, Chapter $chapter, int $pageNumber): Response
    {
        abort_if(!$manga->is_active, 404);
        abort_if(!$chapter->is_active, 404);
        abort_if($chapter->manga_id !== $manga->id, 404);

        $page = $chapter->getPage($pageNumber);

        if (!$page) {
            abort(404, 'Page not found');
        }

        // Determine mime type
        $mimeType = match ($page['extension']) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            default => 'application/octet-stream',
        };

        return response($page['data'], 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000');
    }

    /**
     * Get all pages info for AJAX loading.
     */
    public function pages(Manga $manga, Chapter $chapter): \Illuminate\Http\JsonResponse
    {
        abort_if(!$manga->is_active, 404);
        abort_if(!$chapter->is_active, 404);
        abort_if($chapter->manga_id !== $manga->id, 404);

        $pages = $chapter->extractPages();

        $pagesInfo = array_map(function ($page, $index) use ($manga, $chapter) {
            return [
                'page' => $index,
                'url' => route('chapter.page', [
                    'manga' => $manga->slug,
                    'chapter' => $chapter->slug,
                    'page' => $index,
                ]),
                'name' => $page['name'],
            ];
        }, $pages, array_keys($pages));

        return response()->json([
            'pages' => array_values($pagesInfo),
            'total' => count($pages),
        ]);
    }
}
