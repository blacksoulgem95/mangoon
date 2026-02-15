{{-- resources/views/admin/chapter/create.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Add New Chapter')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">Add New Chapter for "{{ $manga->title }}"</h1>
        <a href="{{ route('admin.manga.chapters.index', $manga) }}" class="btn btn-secondary">
            <span class="terminal-text">Back to Chapter List</span>
        </a>
    </div>

    <form action="{{ route('admin.manga.chapters.store', $manga) }}" method="POST" enctype="multipart/form-data" class="panel p-6 space-y-6">
        @csrf

        {{-- Basic Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="slug" class="block font-medium text-future text-pip-green mb-1">Slug (optional)</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                    class="w-full p-2 bg-bg-screen border border-border rounded text-mono"
                    placeholder="Leave empty for auto-generation">
                @error('slug') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="chapter_number" class="block font-medium text-future text-pip-green mb-1">Chapter Number</label>
                <input type="text" id="chapter_number" name="chapter_number" value="{{ old('chapter_number') }}"
                    class="w-full p-2 bg-bg-screen border border-border rounded text-mono"
                    placeholder="e.g., 1.1, 5, 12">
                @error('chapter_number') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="title" class="block font-medium text-future text-pip-green mb-1">Title (optional)</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}"
                    class="w-full p-2 bg-bg-screen border border-border rounded text-mono"
                    placeholder="Chapter title">
                @error('title') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="volume_number" class="block font-medium text-future text-pip-green mb-1">Volume Number (optional)</label>
                <input type="number" id="volume_number" name="volume_number" value="{{ old('volume_number') }}"
                    class="w-full p-2 bg-bg-screen border border-border rounded text-mono"
                    placeholder="e.g., 1, 2">
                @error('volume_number') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Notes & Release Date --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="notes" class="block font-medium text-future text-pip-green mb-1">Notes (optional)</label>
                <textarea id="notes" name="notes"
                    class="w-full p-2 bg-bg-screen border border-border rounded text-mono"
                    rows="4" placeholder="Any additional info">{{ old('notes') }}</textarea>
                @error('notes') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="release_date" class="block font-medium text-future text-pip-green mb-1">Release Date (optional)</label>
                <input type="date" id="release_date" name="release_date" value="{{ old('release_date') }}"
                    class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                @error('release_date') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- CBZ Upload --}}
        <div>
            <label for="cbz_file" class="block font-medium text-future text-pip-green mb-1">CBZ File (required)</label>
            <input type="file" id="cbz_file" name="cbz_file" accept=".zip,.cbz"
                class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
            @error('cbz_file') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        {{-- Metadata & Flags --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="sort_order" class="block font-medium text-future text-pip-green mb-1">Sort Order (default 0)</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                    class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                @error('sort_order') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="metadata" class="block font-medium text-future text-pip-green mb-1">Metadata (JSON, optional)</label>
                <textarea id="metadata" name="metadata"
                    class="w-full p-2 bg-bg-screen border border-border rounded text-mono"
                    rows="4" placeholder='{"key":"value"}'>{{ old('metadata') }}</textarea>
                @error('metadata') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block font-medium text-future text-pip-green mb-1">Active</label>
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="mr-2">
                <span>Make chapter visible in the library</span>
            </div>

            <div>
                <label class="block font-medium text-future text-pip-green mb-1">Premium</label>
                <input type="checkbox" id="is_premium" name="is_premium" value="1" {{ old('is_premium') ? 'checked' : '' }} class="mr-2">
                <span>Restrict access to subscribers</span>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end space-x-4 mt-6">
            <a href="{{ route('admin.manga.chapters.index', $manga) }}" class="btn btn-secondary">
                <span class="terminal-text">Cancel</span>
            </a>
            <button type="submit" class="btn btn-primary">
                <span class="terminal-text">Create Chapter</span>
            </button>
        </div>
    </form>
</div>
@endsection
