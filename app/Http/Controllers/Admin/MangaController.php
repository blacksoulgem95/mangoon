<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMangaRequest;
use App\Models\Category;
use App\Models\Library;
use App\Models\Manga;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MangaController extends Controller
{
    /**
     * Display a listing of manga in admin.
     */
    public function index(Request $request): View
    {
        $query = Manga::query()
            ->with(['translations', 'source', 'categories', 'tags']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhereHas('translations', function ($tq) use ($search) {
                        $tq->where('title', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->byStatus($status);
        }

        // Filter by active
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $mangas = $query->paginate(50)->withQueryString();

        return view('admin.manga.index', [
            'mangas' => $mangas,
            'filters' => $request->only(['search', 'status', 'is_active', 'sort_by', 'sort_direction']),
        ]);
    }

    /**
     * Show the form for creating a new manga.
     */
    public function create(): View
    {
        $sources = Source::with('translations')->get();
        $categories = Category::with('translations')->get();
        $tags = Tag::with('translations')->get();
        $libraries = Library::with('translations')->get();

        return view('admin.manga.create', [
            'sources' => $sources,
            'categories' => $categories,
            'tags' => $tags,
            'libraries' => $libraries,
        ]);
    }

    /**
     * Store a newly created manga in storage.
     */
    public function store(StoreMangaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($request->input('title', 'manga-' . time()));
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $disk = config('filesystems.manga_disk', config('filesystems.default'));
            $path = $request->file('cover_image')->store('manga/covers', $disk);
            $data['cover_image'] = $path;
        }

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $disk = config('filesystems.manga_disk', config('filesystems.default'));
            $path = $request->file('banner_image')->store('manga/banners', $disk);
            $data['banner_image'] = $path;
        }

        // Extract relationships data
        $categories = $data['categories'] ?? [];
        $tags = $data['tags'] ?? [];
        $libraries = $data['libraries'] ?? [];
        $translations = $data['translations'] ?? [];

        unset($data['categories'], $data['tags'], $data['libraries'], $data['translations']);

        // Create manga
        $manga = Manga::create($data);

        // Attach relationships
        if (!empty($categories)) {
            $manga->categories()->attach($categories);
        }

        if (!empty($tags)) {
            $manga->tags()->attach($tags);
        }

        if (!empty($libraries)) {
            $manga->libraries()->attach($libraries);
        }

        // Create translations
        if (!empty($translations)) {
            foreach ($translations as $translation) {
                if (!empty($translation['language_code']) && !empty($translation['title'])) {
                    $manga->translations()->create($translation);
                }
            }
        }

        return redirect()
            ->route('admin.manga.show', $manga)
            ->with('success', 'Manga created successfully.');
    }

    /**
     * Display the specified manga.
     */
    public function show(Manga $manga): View
    {
        $manga->load([
            'translations',
            'source.translations',
            'categories.translations',
            'tags.translations',
            'libraries.translations',
            'chapters' => function ($query) {
                $query->ordered();
            },
        ]);

        return view('admin.manga.show', [
            'manga' => $manga,
        ]);
    }

    /**
     * Show the form for editing the specified manga.
     */
    public function edit(Manga $manga): View
    {
        $manga->load(['translations', 'categories', 'tags', 'libraries']);

        $sources = Source::with('translations')->get();
        $categories = Category::with('translations')->get();
        $tags = Tag::with('translations')->get();
        $libraries = Library::with('translations')->get();

        return view('admin.manga.edit', [
            'manga' => $manga,
            'sources' => $sources,
            'categories' => $categories,
            'tags' => $tags,
            'libraries' => $libraries,
        ]);
    }

    /**
     * Update the specified manga in storage.
     */
    public function update(StoreMangaRequest $request, Manga $manga): RedirectResponse
    {
        $data = $request->validated();

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $disk = config('filesystems.manga_disk', config('filesystems.default'));

            // Delete old cover
            if ($manga->cover_image) {
                Storage::disk($disk)->delete($manga->cover_image);
            }

            $path = $request->file('cover_image')->store('manga/covers', $disk);
            $data['cover_image'] = $path;
        }

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $disk = config('filesystems.manga_disk', config('filesystems.default'));

            // Delete old banner
            if ($manga->banner_image) {
                Storage::disk($disk)->delete($manga->banner_image);
            }

            $path = $request->file('banner_image')->store('manga/banners', $disk);
            $data['banner_image'] = $path;
        }

        // Extract relationships data
        $categories = $data['categories'] ?? [];
        $tags = $data['tags'] ?? [];
        $libraries = $data['libraries'] ?? [];
        $translations = $data['translations'] ?? [];

        unset($data['categories'], $data['tags'], $data['libraries'], $data['translations']);

        // Update manga
        $manga->update($data);

        // Sync relationships
        if (isset($request->categories)) {
            $manga->categories()->sync($categories);
        }

        if (isset($request->tags)) {
            $manga->tags()->sync($tags);
        }

        if (isset($request->libraries)) {
            $manga->libraries()->sync($libraries);
        }

        // Update or create translations
        if (!empty($translations)) {
            foreach ($translations as $translationData) {
                if (!empty($translationData['language_code']) && !empty($translationData['title'])) {
                    $manga->translations()->updateOrCreate(
                        ['language_code' => $translationData['language_code']],
                        $translationData
                    );
                }
            }
        }

        return redirect()
            ->route('admin.manga.show', $manga)
            ->with('success', 'Manga updated successfully.');
    }

    /**
     * Remove the specified manga from storage.
     */
    public function destroy(Manga $manga): RedirectResponse
    {
        $disk = config('filesystems.manga_disk', config('filesystems.default'));

        // Delete cover image
        if ($manga->cover_image) {
            Storage::disk($disk)->delete($manga->cover_image);
        }

        // Delete banner image
        if ($manga->banner_image) {
            Storage::disk($disk)->delete($manga->banner_image);
        }

        $manga->delete();

        return redirect()
            ->route('admin.manga.index')
            ->with('success', 'Manga deleted successfully.');
    }

    /**
     * Restore the specified soft deleted manga.
     */
    public function restore(int $id): RedirectResponse
    {
        $manga = Manga::withTrashed()->findOrFail($id);
        $manga->restore();

        return redirect()
            ->route('admin.manga.show', $manga)
            ->with('success', 'Manga restored successfully.');
    }

    /**
     * Permanently delete the specified manga.
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $manga = Manga::withTrashed()->findOrFail($id);

        $disk = config('filesystems.manga_disk', config('filesystems.default'));

        // Delete cover image
        if ($manga->cover_image) {
            Storage::disk($disk)->delete($manga->cover_image);
        }

        // Delete banner image
        if ($manga->banner_image) {
            Storage::disk($disk)->delete($manga->banner_image);
        }

        $manga->forceDelete();

        return redirect()
            ->route('admin.manga.index')
            ->with('success', 'Manga permanently deleted.');
    }

    /**
     * Toggle manga active status.
     */
    public function toggleActive(Manga $manga): RedirectResponse
    {
        $manga->update(['is_active' => !$manga->is_active]);

        $status = $manga->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Manga {$status} successfully.");
    }

    /**
     * Toggle manga featured status.
     */
    public function toggleFeatured(Manga $manga): RedirectResponse
    {
        $manga->update(['is_featured' => !$manga->is_featured]);

        $status = $manga->is_featured ? 'featured' : 'unfeatured';

        return back()->with('success', "Manga {$status} successfully.");
    }
}
