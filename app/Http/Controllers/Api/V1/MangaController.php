<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Manga;
use App\Models\MangaVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MangaController extends Controller
{
    public function index(Request $request)
    {
        $query = Manga::query()->with(['translations', 'externalSources']);

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('library_id')) {
            $query->where('library_id', $request->library_id);
        }

        if ($request->has('is_mature')) {
            $query->where('is_mature', $request->is_mature);
        }

        $perPage = $request->get('per_page', 20);
        $manga = $query->paginate($perPage);

        return response()->json($manga);
    }

    public function show(Manga $manga)
    {
        $manga->load(['translations', 'chapters', 'externalSources', 'versions']);
        
        return response()->json($manga);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'synopsis' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'is_mature' => 'boolean',
            'library_id' => 'nullable|exists:libraries,id',
        ]);

        $manga = Manga::create($validated);

        return response()->json($manga, 201);
    }

    public function update(Request $request, Manga $manga)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'synopsis' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'is_mature' => 'boolean',
            'library_id' => 'nullable|exists:libraries,id',
        ]);

        $manga->update($validated);

        return response()->json($manga);
    }

    public function destroy(Manga $manga)
    {
        $manga->delete();

        return response()->json(['message' => 'Manga deleted successfully']);
    }

    public function versions(Manga $manga)
    {
        $versions = $manga->versions()->with('relatedManga')->get();

        return response()->json([
            'data' => $versions,
        ]);
    }

    public function linkVersion(Request $request, Manga $manga)
    {
        $validated = $request->validate([
            'related_manga_id' => 'required|exists:mangas,id',
            'version_type' => 'required|in:translation,adaptation,spin-off',
            'language' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($manga->id === $validated['related_manga_id']) {
            return response()->json(['error' => 'Cannot link manga to itself'], 422);
        }

        $manga->versions()->attach($validated['related_manga_id'], [
            'version_type' => $validated['version_type'],
            'language' => $validated['language'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['message' => 'Version linked successfully']);
    }

    public function unlinkVersion(Manga $manga, int $versionId)
    {
        $manga->versions()->detach($versionId);

        return response()->json(['message' => 'Version unlinked successfully']);
    }

    public function mergeSuggestions(Request $request)
    {
        $mangaId = $request->get('manga_id');
        
        if (!$mangaId) {
            return response()->json(['error' => 'manga_id required'], 400);
        }

        $manga = Manga::findOrFail($mangaId);

        $suggestions = Manga::query()
            ->where('id', '!=', $mangaId)
            ->where(function ($query) use ($manga) {
                $query->whereRaw('LOWER(title) = LOWER(?)', [$manga->title])
                      ->orWhere('author', $manga->author)
                      ->orWhereHas('externalSources', function ($q) use ($manga) {
                          $q->where('metadata->title', $manga->title);
                      });
            })
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $suggestions,
            'manga' => $manga,
        ]);
    }
}
