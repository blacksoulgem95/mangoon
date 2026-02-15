{{-- resources/views/manga/chapters.blade.php --}}
@extends('layouts.app')

@section('title', ' • ' . $manga->title . ' Chapters')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-future text-3xl font-bold mb-6 glow-text">{{ $manga->title }} – Chapters</h1>

    <div class="panel p-4 mb-6">
        <h2 class="text-future text-2xl mb-4">Chapter List</h2>
        @if($chapters->count())
            <ul class="space-y-4">
                @foreach($chapters as $chapter)
                    <li class="flex items-center justify-between border-b border-border py-3">
                        <a href="{{ route('chapter.show', [$manga, $chapter]) }}" class="flex-1 text-pip-green hover:underline">
                            <span class="text-future font-semibold">{{ $chapter->displayName() }}</span>
                        </a>
                        <div class="flex space-x-2">
                            @if($chapter->is_premium)
                                <span class="badge badge-danger">Premium</span>
                            @else
                                <span class="badge badge-success">Free</span>
                            @endif
                            <span class="badge badge-primary">{{ $chapter->sort_order }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-6">
                {{ $chapters->withQueryString()->links('vendor.pagination.default') }}
            </div>
        @else
            <p class="text-text-primary text-lg">No chapters available.</p>
        @endif
    </div>

    <div class="flex space-x-4">
        <a href="{{ route('manga.show', $manga) }}" class="btn btn-secondary">
            &larr; Back to Manga
        </a>
        @auth
            <a href="{{ route('admin.manga.chapters.create', $manga) }}" class="btn btn-primary">
                Add Chapter
            </a>
        @endauth
    </div>
</div>
@endsection
