<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChapterListController extends Controller
{
    /**
     * Display a listing of all chapters.
     */
    public function index(Request $request): View
    {
        $query = Chapter::query()->with(['manga', 'translations']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('manga', function ($m) use ($search) {
                        $m->where('title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('translations', function ($t) use ($search) {
                        $t->where('title', 'like', "%{$search}%");
                    });
            });
        }

        // Active filter
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $chapters = $query->paginate(50)->withQueryString();

        return view('admin.chapters.index', [
            'chapters' => $chapters,
            'filters' => $request->only(['search', 'is_active', 'sort_by', 'sort_direction']),
        ]);
    }

    /**
     * Display the specified chapter.
     */
    public function show(Chapter $chapter): View
    {
        $chapter->load(['manga', 'translations']);

        return view('admin.chapters.show', [
            'chapter' => $chapter,
        ]);
    }
}
