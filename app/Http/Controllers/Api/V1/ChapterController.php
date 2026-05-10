<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Services\CbzProcessor;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function pages(Chapter $chapter, CbzProcessor $cbzProcessor)
    {
        try {
            $manga = $chapter->manga;
            $pageCount = $cbzProcessor->getPageCount($manga->id, (string)$chapter->id);
            
            return response()->json([
                'chapter_id' => $chapter->id,
                'page_count' => $pageCount,
                'pages' => range(1, $pageCount),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load pages',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function page(Chapter $chapter, int $page, CbzProcessor $cbzProcessor)
    {
        try {
            $manga = $chapter->manga;
            $url = $cbzProcessor->getPageUrl($manga->id, (string)$chapter->id, $page);
            
            return response()->json([
                'page' => $page,
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Page not found',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function downloadFromSource(Chapter $chapter, Request $request)
    {
        return response()->json([
            'error' => 'Download from source not implemented in this endpoint',
            'message' => 'Use external source download API instead',
        ], 400);
    }
}
