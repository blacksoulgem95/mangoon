{{-- resources/views/admin/manga/show.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Manga Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">Manga Details</h1>
        <div class="space-x-2">
            <a href="{{ route('admin.manga.chapters.index', $manga) }}" class="btn btn-primary">
                <span class="terminal-text">Manage Chapters</span>
            </a>
            <a href="{{ route('admin.manga.edit', $manga) }}" class="btn btn-secondary">
                <span class="terminal-text">Edit</span>
            </a>
            <form action="{{ route('admin.manga.destroy', $manga) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this manga?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <span class="terminal-text">Delete</span>
                </button>
            </form>
            <form action="{{ route('admin.manga.toggle-active', $manga) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" class="btn btn-sm {{ $manga->is_active ? 'btn-danger' : 'btn-primary' }}">
                    <span class="terminal-text">{{ $manga->is_active ? 'Deactivate' : 'Activate' }}</span>
                </button>
            </form>
            <form action="{{ route('admin.manga.toggle-featured', $manga) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" class="btn btn-sm {{ $manga->is_featured ? 'btn-danger' : 'btn-primary' }}">
                    <span class="terminal-text">{{ $manga->is_featured ? 'Unfeature' : 'Feature' }}</span>
                </button>
            </form>
        </div>
    </div>

    <div class="panel p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Cover and Banner --}}
            <div class="md:col-span-1">
                <div class="mb-4">
                    <h2 class="text-future font-semibold text-pip-green mb-2">Cover Image</h2>
                    @if($manga->cover_image_url)
                        <img src="{{ $manga->cover_image_url }}" alt="{{ $manga->title }} cover" class="w-full h-auto object-cover rounded">
                    @else
                        <p class="text-text-primary">No cover image</p>
                    @endif
                </div>
                <div>
                    <h2 class="text-future font-semibold text-pip-green mb-2">Banner Image</h2>
                    @if($manga->banner_image_url)
                        <img src="{{ $manga->banner_image_url }}" alt="{{ $manga->title }} banner" class="w-full h-auto object-cover rounded">
                    @else
                        <p class="text-text-primary">No banner image</p>
                    @endif
                </div>
            </div>

            {{-- Metadata --}}
            <div class="md:col-span-2 space-y-4">
                <h2 class="text-future font-semibold text-pip-green mb-2">Basic Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-text-primary">
                    <dt class="font-medium">Title:</dt>
                    <dd>{{ $manga->title }}</dd>

                    <dt class="font-medium">Author:</dt>
                    <dd>{{ $manga->author ?? '-' }}</dd>

                    <dt class="font-medium">Illustrator:</dt>
                    <dd>{{ $manga->illustrator ?? '-' }}</dd>

                    <dt class="font-medium">Publisher:</dt>
                    <dd>{{ $manga->publisher ?? '-' }}</dd>

                    <dt class="font-medium">Publication Year:</dt>
                    <dd>{{ $manga->publication_year ?? '-' }}</dd>

                    <dt class="font-medium">Status:</dt>
                    <dd>{{ ucfirst($manga->status) }}</dd>

                    <dt class="font-medium">Rating:</dt>
                    <dd>{{ number_format($manga->rating ?? 0, 2) }}</dd>

                    <dt class="font-medium">Views:</dt>
                    <dd>{{ $manga->views_count }}</dd>

                    <dt class="font-medium">Favorites:</dt>
                    <dd>{{ $manga->favorites_count }}</dd>
                </dl>

                <h2 class="text-future font-semibold text-pip-green mb-2">Relationships</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-text-primary">
                    <div>
                        <h3 class="text-future font-medium text-pip-green">Categories</h3>
                        @forelse($manga->categories as $cat)
                            <span class="badge badge-success">{{ $cat->name }}</span>
                        @empty
                            <p class="text-text-primary">None</p>
                        @endforelse
                    </div>
                    <div>
                        <h3 class="text-future font-medium text-pip-green">Tags</h3>
                        @forelse($manga->tags as $tag)
                            <span class="badge badge-warning">{{ $tag->name }}</span>
                        @empty
                            <p class="text-text-primary">None</p>
                        @endforelse
                    </div>
                    <div>
                        <h3 class="text-future font-medium text-pip-green">Libraries</h3>
                        @forelse($manga->libraries as $lib)
                            <span class="badge badge-primary">{{ $lib->name }}</span>
                        @empty
                            <p class="text-text-primary">None</p>
                        @endforelse
                    </div>
                </div>

                <h2 class="text-future font-semibold text-pip-green mb-2">Translations</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-text-primary">
                    @forelse($manga->translations as $tr)
                        <div>
                            <h3 class="text-future font-medium text-pip-green">{{ $tr->language_code }}</h3>
                            <p class="font-semibold">{{ $tr->title }}</p>
                            <p class="text-sm opacity-80">{{ Str::limit($tr->synopsis, 120) }}</p>
                        </div>
                    @empty
                        <p class="text-text-primary">No translations available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Chapters List --}}
        <div class="mt-8">
            <h2 class="text-future font-semibold text-pip-green mb-4">Chapters ({{ $manga->chapters->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="p-3 text-future font-semibold text-pip-green">Cover</th>
                            <th class="p-3 text-future font-semibold text-pip-green">Title</th>
                            <th class="p-3 text-future font-semibold text-pip-green">Volume</th>
                            <th class="p-3 text-future font-semibold text-pip-green">Sort Order</th>
                            <th class="p-3 text-future font-semibold text-pip-green">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($manga->chapters as $chapter)
                            <tr class="border-b border-border hover:bg-bg-panel">
                                <td class="p-3 text-center">
                                    @if($chapter->cover_image_url)
                                        <img src="{{ $chapter->cover_image_url }}" alt="{{ $chapter->title }}" class="w-12 h-16 object-cover mx-auto rounded">
                                    @else
                                        <p class="text-text-primary">N/A</p>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <a href="{{ route('admin.manga.chapters.show', [$manga, $chapter]) }}" class="text-pip-green hover:underline">
                                        {{ $chapter->displayName() }}
                                    </a>
                                </td>
                                <td class="p-3">{{ $chapter->volume_number ?? '-' }}</td>
                                <td class="p-3">{{ $chapter->sort_order }}</td>
                                <td class="p-3 space-x-2">
                                    <a href="{{ route('admin.manga.chapters.edit', [$manga, $chapter]) }}" class="btn btn-secondary btn-sm">
                                        <span class="terminal-text">Edit</span>
                                    </a>
                                    <form action="{{ route('admin.manga.chapters.destroy', [$manga, $chapter]) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this chapter?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <span class="terminal-text">Delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-text-primary">No chapters found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
