<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChapterRequest;
use App\Models\Chapter;
use App\Models\Manga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChapterController extends Controller
{
    /**
     * Display a listing of chapters for a manga.
     */
    public function index(Manga $manga): View
    {
        $chapters = $manga->chapters()
            ->ordered()
            ->paginate(100);

        return view('admin.chapter.index', [
            'manga' => $manga,
            'chapters' => $chapters,
        ]);
    }

    /**
     * Show the form for creating a new chapter.
     */
    public function create(Manga $manga): View
    {
        return view('admin.chapter.create', [
            'manga' => $manga,
        ]);
    }

    /**
     * Store a newly created chapter in storage.
     */
    public function store(StoreChapterRequest $request, Manga $manga): RedirectResponse
    {
        $data = $request->validated();
        $data['manga_id'] = $manga->id;

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $baseSlug = Str::slug($manga->slug . '-chapter-' . $data['chapter_number']);
            $slug = $baseSlug;
            $counter = 1;

            while (Chapter::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $data['slug'] = $slug;
        }

        // Determine storage disk
        $storageDisk = config('filesystems.manga_disk', config('filesystems.default'));
        $data['storage_disk'] = $storageDisk;

        // Handle CBZ file upload
        if ($request->hasFile('cbz_file')) {
            $file = $request->file('cbz_file');

            // Generate unique filename
            $filename = Str::slug($manga->slug . '-' . $data['chapter_number']) . '-' . time() . '.cbz';
            $path = "manga/{$manga->slug}/chapters/{$filename}";

            // Store the file
            $storedPath = Storage::disk($storageDisk)->putFileAs(
                "manga/{$manga->slug}/chapters",
                $file,
                $filename
            );

            $data['cbz_file_path'] = $storedPath;
            $data['file_size'] = $file->getSize();

            // Create chapter first to get ID for page extraction
            $chapter = Chapter::create($data);

            // Extract and count pages
            try {
                $chapter->updatePageCount();
            } catch (\Exception $e) {
                // If page count extraction fails, we'll keep it as 0
                \Log::warning('Failed to extract page count for chapter: ' . $e->getMessage());
            }
        } else {
            return back()
                ->withInput()
                ->withErrors(['cbz_file' => 'CBZ file is required.']);
        }

        return redirect()
            ->route('admin.manga.chapters.index', $manga)
            ->with('success', 'Chapter created successfully.');
    }

    /**
     * Display the specified chapter.
     */
    public function show(Manga $manga, Chapter $chapter): View
    {
        abort_if($chapter->manga_id !== $manga->id, 404);

        return view('admin.chapter.show', [
            'manga' => $manga,
            'chapter' => $chapter,
        ]);
    }

    /**
     * Show the form for editing the specified chapter.
     */
    public function edit(Manga $manga, Chapter $chapter): View
    {
        abort_if($chapter->manga_id !== $manga->id, 404);

        return view('admin.chapter.edit', [
            'manga' => $manga,
            'chapter' => $chapter,
        ]);
    }

    /**
     * Update the specified chapter in storage.
     */
    public function update(StoreChapterRequest $request, Manga $manga, Chapter $chapter): RedirectResponse
    {
        abort_if($chapter->manga_id !== $manga->id, 404);

        $data = $request->validated();

        // Handle CBZ file replacement
        if ($request->hasFile('cbz_file')) {
            $file = $request->file('cbz_file');
            $storageDisk = $chapter->storage_disk;

            // Delete old CBZ file
            if ($chapter->cbz_file_path && Storage::disk($storageDisk)->exists($chapter->cbz_file_path)) {
                Storage::disk($storageDisk)->delete($chapter->cbz_file_path);
            }

            // Generate unique filename
            $filename = Str::slug($manga->slug . '-' . $data['chapter_number']) . '-' . time() . '.cbz';

            // Store the new file
            $storedPath = Storage::disk($storageDisk)->putFileAs(
                "manga/{$manga->slug}/chapters",
                $file,
                $filename
            );

            $data['cbz_file_path'] = $storedPath;
            $data['file_size'] = $file->getSize();

            // Update chapter
            $chapter->update($data);

            // Re-extract and count pages
            try {
                $chapter->updatePageCount();
            } catch (\Exception $e) {
                \Log::warning('Failed to extract page count for chapter: ' . $e->getMessage());
            }
        } else {
            // Update without file
            $chapter->update($data);
        }

        return redirect()
            ->route('admin.manga.chapters.show', [$manga, $chapter])
            ->with('success', 'Chapter updated successfully.');
    }

    /**
     * Remove the specified chapter from storage.
     */
    public function destroy(Manga $manga, Chapter $chapter): RedirectResponse
    {
        abort_if($chapter->manga_id !== $manga->id, 404);

        $storageDisk = $chapter->storage_disk;

        // Delete CBZ file
        if ($chapter->cbz_file_path && Storage::disk($storageDisk)->exists($chapter->cbz_file_path)) {
            Storage::disk($storageDisk)->delete($chapter->cbz_file_path);
        }

        $chapter->delete();

        return redirect()
            ->route('admin.manga.chapters.index', $manga)
            ->with('success', 'Chapter deleted successfully.');
    }

    /**
     * Restore the specified soft deleted chapter.
     */
    public function restore(Manga $manga, int $chapterId): RedirectResponse
    {
        $chapter = Chapter::withTrashed()->findOrFail($chapterId);

        abort_if($chapter->manga_id !== $manga->id, 404);

        $chapter->restore();

        return redirect()
            ->route('admin.manga.chapters.show', [$manga, $chapter])
            ->with('success', 'Chapter restored successfully.');
    }

    /**
     * Permanently delete the specified chapter.
     */
    public function forceDelete(Manga $manga, int $chapterId): RedirectResponse
    {
        $chapter = Chapter::withTrashed()->findOrFail($chapterId);

        abort_if($chapter->manga_id !== $manga->id, 404);

        $storageDisk = $chapter->storage_disk;

        // Delete CBZ file
        if ($chapter->cbz_file_path && Storage::disk($storageDisk)->exists($chapter->cbz_file_path)) {
            Storage::disk($storageDisk)->delete($chapter->cbz_file_path);
        }

        $chapter->forceDelete();

        return redirect()
            ->route('admin.manga.chapters.index', $manga)
            ->with('success', 'Chapter permanently deleted.');
    }

    /**
     * Toggle chapter active status.
     */
    public function toggleActive(Manga $manga, Chapter $chapter): RedirectResponse
    {
        abort_if($chapter->manga_id !== $manga->id, 404);

        $chapter->update(['is_active' => !$chapter->is_active]);

        $status = $chapter->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Chapter {$status} successfully.");
    }

    /**
     * Bulk upload chapters from multiple CBZ files.
     */
    public function bulkUpload(Request $request, Manga $manga): RedirectResponse
    {
        $request->validate([
            'cbz_files' => 'required|array|min:1',
            'cbz_files.*' => 'required|file|mimes:zip,cbz|max:512000', // 500MB max per file
        ]);

        $storageDisk = config('filesystems.manga_disk', config('filesystems.default'));
        $uploadedCount = 0;
        $errors = [];

        foreach ($request->file('cbz_files') as $index => $file) {
            try {
                // Extract chapter number from filename (e.g., "chapter-001.cbz" or "001.cbz")
                $filename = $file->getClientOriginalName();
                preg_match('/(\d+)/', $filename, $matches);
                $chapterNumber = $matches[1] ?? ($index + 1);

                // Generate slug
                $baseSlug = Str::slug($manga->slug . '-chapter-' . $chapterNumber);
                $slug = $baseSlug;
                $counter = 1;

                while (Chapter::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                // Store file
                $storedFilename = Str::slug($manga->slug . '-' . $chapterNumber) . '-' . time() . '-' . $index . '.cbz';
                $storedPath = Storage::disk($storageDisk)->putFileAs(
                    "manga/{$manga->slug}/chapters",
                    $file,
                    $storedFilename
                );

                // Create chapter
                $chapter = Chapter::create([
                    'manga_id' => $manga->id,
                    'slug' => $slug,
                    'chapter_number' => $chapterNumber,
                    'cbz_file_path' => $storedPath,
                    'storage_disk' => $storageDisk,
                    'file_size' => $file->getSize(),
                    'sort_order' => $chapterNumber,
                    'is_active' => true,
                ]);

                // Extract page count
                try {
                    $chapter->updatePageCount();
                } catch (\Exception $e) {
                    \Log::warning('Failed to extract page count: ' . $e->getMessage());
                }

                $uploadedCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to upload {$filename}: " . $e->getMessage();
            }
        }

        if ($uploadedCount > 0) {
            $message = "{$uploadedCount} chapter(s) uploaded successfully.";
            if (!empty($errors)) {
                $message .= ' Some files failed: ' . implode(', ', $errors);
            }

            return redirect()
                ->route('admin.manga.chapters.index', $manga)
                ->with('success', $message);
        }

        return back()
            ->withErrors(['cbz_files' => 'Failed to upload chapters: ' . implode(', ', $errors)]);
    }

    /**
     * Update chapter sort order.
     */
    public function updateOrder(Request $request, Manga $manga): RedirectResponse
    {
        $request->validate([
            'chapters' => 'required|array',
            'chapters.*.id' => 'required|exists:chapters,id',
            'chapters.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->input('chapters', []) as $chapterData) {
            Chapter::where('id', $chapterData['id'])
                ->where('manga_id', $manga->id)
                ->update(['sort_order' => $chapterData['sort_order']]);
        }

        return back()->with('success', 'Chapter order updated successfully.');
    }
}
