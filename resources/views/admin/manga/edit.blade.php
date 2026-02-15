{{-- resources/views/admin/manga/edit.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Edit Manga')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-future text-3xl font-semibold text-pip-green mb-6">Edit Manga: {{ $manga->title }}</h1>

    <form action="{{ route('admin.manga.update', $manga) }}" method="POST" enctype="multipart/form-data" class="panel p-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- Basic Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="slug" class="block font-medium text-future text-pip-green mb-1">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $manga->slug) }}" class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                @error('slug') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="source_id" class="block font-medium text-future text-pip-green mb-1">Source</label>
                <select id="source_id" name="source_id" class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                    <option value="">-- Select Source --</option>
                    @foreach(\App\Models\Source::with('translations')->get() as $source)
                        <option value="{{ $source->id }}" {{ old('source_id', $manga->source_id) == $source->id ? 'selected' : '' }}>
                            {{ $source->translations->first()->title ?? $source->name }}
                        </option>
                    @endforeach
                </select>
                @error('source_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="author" class="block font-medium text-future text-pip-green mb-1">Author</label>
                <input type="text" id="author" name="author" value="{{ old('author', $manga->author) }}" class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                @error('author') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="illustrator" class="block font-medium text-future text-pip-green mb-1">Illustrator</label>
                <input type="text" id="illustrator" name="illustrator" value="{{ old('illustrator', $manga->illustrator) }}" class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                @error('illustrator') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Images --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="cover_image" class="block font-medium text-future text-pip-green mb-1">Cover Image</label>
                <input type="file" id="cover_image" name="cover_image" accept="image/*" class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                @error('cover_image') <span class="text-danger">{{ $message }}</span> @enderror
                @if($manga->cover_image_url)
                    <img src="{{ $manga->cover_image_url }}" alt="{{ $manga->title }}" class="mt-2 w-32 h-auto rounded">
                @endif
            </div>

            <div>
                <label for="banner_image" class="block font-medium text-future text-pip-green mb-1">Banner Image</label>
                <input type="file" id="banner_image" name="banner_image" accept="image/*" class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                @error('banner_image') <span class="text-danger">{{ $message }}</span> @enderror
                @if($manga->banner_image_url)
                    <img src="{{ $manga->banner_image_url }}" alt="{{ $manga->title }} banner" class="mt-2 w-32 h-auto rounded">
                @endif
            </div>
        </div>

        {{-- Publication & Status --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="publication_year" class="block font-medium text-future text-pip-green mb-1">Publication Year</label>
                <input type="number" id="publication_year" name="publication_year" value="{{ old('publication_year', $manga->publication_year) }}" class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                @error('publication_year') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="status" class="block font-medium text-future text-pip-green mb-1">Status</label>
                <select id="status" name="status" class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                    <option value="">-- Select Status --</option>
                    @foreach(['ongoing', 'completed', 'hiatus', 'cancelled', 'upcoming'] as $status)
                        <option value="{{ $status }}" {{ old('status', $manga->status) == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
                @error('status') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Relationships --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="categories" class="block font-medium text-future text-pip-green mb-1">Categories</label>
                <select id="categories" name="categories[]" multiple class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                    @foreach(\App\Models\Category::with('translations')->get() as $cat)
                        <option value="{{ $cat->id }}"
                            {{ in_array($cat->id, old('categories', $manga->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $cat->translations->first()->title ?? $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('categories') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="tags" class="block font-medium text-future text-pip-green mb-1">Tags</label>
                <select id="tags" name="tags[]" multiple class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                    @foreach(\App\Models\Tag::with('translations')->get() as $tag)
                        <option value="{{ $tag->id }}"
                            {{ in_array($tag->id, old('tags', $manga->tags->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $tag->translations->first()->title ?? $tag->name }}
                        </option>
                    @endforeach
                </select>
                @error('tags') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="libraries" class="block font-medium text-future text-pip-green mb-1">Libraries</label>
                <select id="libraries" name="libraries[]" multiple class="w-full p-2 bg-bg-screen border border-border rounded text-mono">
                    @foreach(\App\Models\Library::with('translations')->get() as $lib)
                        <option value="{{ $lib->id }}"
                            {{ in_array($lib->id, old('libraries', $manga->libraries->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $lib->translations->first()->title ?? $lib->name }}
                        </option>
                    @endforeach
                </select>
                @error('libraries') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Translations --}}
        <div class="mt-6">
            <h2 class="text-future text-2xl font-semibold text-pip-green mb-3">Translations</h2>
            <div id="translationContainer" class="space-y-4">
                @php
                    $oldTranslations = old('translations', $manga->translations->toArray());
                @endphp
                @foreach($oldTranslations as $index => $translation)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-border pt-4">
                        <div>
                            <label class="block font-medium text-future text-pip-green mb-1">Language Code</label>
                            <input type="text" name="translations[{{ $index }}][language_code]"
                                   value="{{ $translation['language_code'] ?? '' }}"
                                   class="w-full p-2 bg-bg-screen border border-border rounded text-mono" placeholder="e.g., en">
                            @error("translations.{$index}.language_code") <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-future text-pip-green mb-1">Title</label>
                            <input type="text" name="translations[{{ $index }}][title]"
                                   value="{{ $translation['title'] ?? '' }}"
                                   class="w-full p-2 bg-bg-screen border border-border rounded text-mono" placeholder="Title in language">
                            @error("translations.{$index}.title") <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-future text-pip-green mb-1">Synopsis</label>
                            <textarea name="translations[{{ $index }}][synopsis]"
                                      class="w-full p-2 bg-bg-screen border border-border rounded text-mono"
                                      placeholder="Short synopsis">{{ $translation['synopsis'] ?? '' }}</textarea>
                            @error("translations.{$index}.synopsis") <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" id="addTranslationBtn" class="btn btn-secondary mt-4">
                <span class="terminal-text">Add Another Translation</span>
            </button>
        </div>

        {{-- Flags --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div>
                <label class="block font-medium text-future text-pip-green mb-1">Active</label>
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       {{ old('is_active', $manga->is_active) ? 'checked' : '' }} class="mr-2">
                <span>Show in catalogue</span>
            </div>

            <div>
                <label class="block font-medium text-future text-pip-green mb-1">Featured</label>
                <input type="checkbox" id="is_featured" name="is_featured" value="1"
                       {{ old('is_featured', $manga->is_featured) ? 'checked' : '' }} class="mr-2">
                <span>Highlight on homepage</span>
            </div>

            <div>
                <label class="block font-medium text-future text-pip-green mb-1">Mature</label>
                <input type="checkbox" id="is_mature" name="is_mature" value="1"
                       {{ old('is_mature', $manga->is_mature) ? 'checked' : '' }} class="mr-2">
                <span>Requires age verification</span>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end space-x-4 mt-6">
            <a href="{{ route('admin.manga.index') }}" class="btn btn-secondary">
                <span class="terminal-text">Cancel</span>
            </a>
            <button type="submit" class="btn btn-primary">
                <span class="terminal-text">Save Changes</span>
            </button>
        </div>
    </form>
</div>

{{-- JavaScript for dynamic translations --}}
<script>
    let translationIndex = {{ count($oldTranslations) }};
    const container = document.getElementById('translationContainer');
    const addBtn = document.getElementById('addTranslationBtn');

    addBtn.addEventListener('click', () => {
        const div = document.createElement('div');
        div.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-border pt-4';
        div.innerHTML = `
            <div>
                <label class="block font-medium text-future text-pip-green mb-1">Language Code</label>
                <input type="text" name="translations[${translationIndex}][language_code]" class="w-full p-2 bg-bg-screen border border-border rounded text-mono" placeholder="e.g., en">
            </div>
            <div>
                <label class="block font-medium text-future text-pip-green mb-1">Title</label>
                <input type="text" name="translations[${translationIndex}][title]" class="w-full p-2 bg-bg-screen border border-border rounded text-mono" placeholder="Title in language">
            </div>
            <div>
                <label class="block font-medium text-future text-pip-green mb-1">Synopsis</label>
                <textarea name="translations[${translationIndex}][synopsis]" class="w-full p-2 bg-bg-screen border border-border rounded text-mono" placeholder="Short synopsis"></textarea>
            </div>
        `;
        container.appendChild(div);
        translationIndex++;
    });
</script>
@endsection
