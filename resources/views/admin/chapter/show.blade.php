{{-- resources/views/admin/chapter/show.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Chapter Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">Chapter Details</h1>
        <div class="space-x-2">
            <a href="{{ route('admin.manga.chapters.edit', [$manga, $chapter]) }}" class="btn btn-secondary">
                <span class="terminal-text">Edit</span>
            </a>
            <form action="{{ route('admin.manga.chapters.destroy', [$manga, $chapter]) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this chapter?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <span class="terminal-text">Delete</span>
                </button>
            </form>
            <form action="{{ route('admin.manga.chapters.toggle-active', [$manga, $chapter]) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" class="btn btn-sm {{ $chapter->is_active ? 'btn-danger' : 'btn-primary' }}">
                    <span class="terminal-text">{{ $chapter->is_active ? 'Deactivate' : 'Activate' }}</span>
                </button>
            </form>
        </div>
    </div>

    <div class="panel p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Cover Image --}}
            <div class="md:col-span-1">
                <h2 class="text-future font-semibold text-pip-green mb-2">Cover Image</h2>
                @if($chapter->cover_image_url)
                    <img src="{{ $chapter->cover_image_url }}" alt="{{ $chapter->title }} cover" class="w-full h-auto object-cover rounded">
                @else
                    <p class="text-text-primary">No cover image</p>
                @endif
            </div>

            {{-- Metadata --}}
            <div class="md:col-span-2 space-y-4">
                <h2 class="text-future font-semibold text-pip-green mb-2">Basic Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-text-primary">
                    <dt class="font-medium">Title:</dt>
                    <dd>{{ $chapter->title ?? '-' }}</dd>

                    <dt class="font-medium">Chapter Number:</dt>
                    <dd>{{ $chapter->chapter_number }}</dd>

                    <dt class="font-medium">Volume Number:</dt>
                    <dd>{{ $chapter->volume_number ?? '-' }}</dd>

                    <dt class="font-medium">Sort Order:</dt>
                    <dd>{{ $chapter->sort_order }}</dd>

                    <dt class="font-medium">Page Count:</dt>
                    <dd>{{ $chapter->page_count }}</dd>

                    <dt class="font-medium">Release Date:</dt>
                    <dd>{{ $chapter->release_date ? $chapter->release_date->format('F j, Y') : '-' }}</dd>

                    <dt class="font-medium">Notes:</dt>
                    <dd>{{ $chapter->notes ?? '-' }}</dd>

                    <dt class="font-medium">Metadata:</dt>
                    <dd>
                        @if($chapter->metadata)
                            <pre class="text-sm whitespace-pre-wrap">{{ json_encode($chapter->metadata, JSON_PRETTY_PRINT) }}</pre>
                        @else
                            <span class="text-text-primary">None</span>
                        @endif
                    </dd>

                    <dt class="font-medium">Is Premium:</dt>
                    <dd>
                        @if($chapter->is_premium)
                            <span class="badge badge-danger">Yes</span>
                        @else
                            <span class="badge badge-success">No</span>
                        @endif
                    </dd>

                    <dt class="font-medium">Is Active:</dt>
                    <dd>
                        @if($chapter->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end space-x-4 mt-4">
            <a href="{{ route('admin.manga.chapters.index', $manga) }}" class="btn btn-secondary">
                <span class="terminal-text">Back to Chapters List</span>
            </a>
        </div>
    </div>
</div>
@endsection
