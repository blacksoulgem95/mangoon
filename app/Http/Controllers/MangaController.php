<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MangaController extends Controller
{
    /**
     * Display a listing of manga.
     */
    public function index(Request $request): View
    {
        $query = Manga::query()
            ->with(['translations', 'tags', 'categories', 'source'])
            ->active();

        // Search
        if ($search = $request->input('search')) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('synopsis', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($categorySlug = $request->input('category')) {
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('categories.slug', $categorySlug);
            });
        }

        // Filter by tag
        if ($tagSlug = $request->input('tag')) {
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                $q->where('tags.slug', $tagSlug);
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->byStatus($status);
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->byType($type);
        }

        // Filter mature content
        if (!$request->boolean('show_mature')) {
            $query->safe();
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'popular' => $query->popular(),
            'views' => $query->mostViewed(),
            'favorites' => $query->mostFavorited(),
            'rating' => $query->orderByDesc('rating'),
            default => $query->latest(),
        };

        $mangas = $query->paginate(24);

        return view('manga.index', [
            'mangas' => $mangas,
            'filters' => $request->only(['search', 'category', 'tag', 'status', 'type', 'sort', 'show_mature']),
        ]);
    }

    /**
     * Display the specified manga.
     */
    public function show(Manga $manga): View
    {
        abort_if(!$manga->is_active, 404);

        $manga->load([
            'translations',
            'tags.translations',
            'categories.translations',
            'source.translations',
            'chapters' => function ($query) {
                $query->active()->ordered()->limit(50);
            },
        ]);

        // Increment views
        $manga->incrementViews();

        // Get related manga
        $relatedManga = Manga::query()
            ->where('id', '!=', $manga->id)
            ->active()
            ->where(function ($query) use ($manga) {
                // Same categories or tags
                $query->whereHas('categories', function ($q) use ($manga) {
                    $q->whereIn('categories.id', $manga->categories->pluck('id'));
                })->orWhereHas('tags', function ($q) use ($manga) {
                    $q->whereIn('tags.id', $manga->tags->pluck('id'));
                });
            })
            ->with(['translations'])
            ->inRandomOrder()
            ->limit(6)
            ->get();

        return view('manga.show', [
            'manga' => $manga,
            'relatedManga' => $relatedManga,
        ]);
    }

    /**
     * Display manga chapters list.
     */
    public function chapters(Manga $manga): View
    {
        abort_if(!$manga->is_active, 404);

        $chapters = $manga->chapters()
            ->active()
            ->ordered()
            ->paginate(100);

        return view('manga.chapters', [
            'manga' => $manga,
            'chapters' => $chapters,
        ]);
    }
}
