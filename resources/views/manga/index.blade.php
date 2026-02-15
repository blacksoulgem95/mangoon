{{-- resources/views/manga/index.blade.php --}}
@extends('layouts.app')

@section('title', ' • Browse Manga')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-future text-4xl text-center mb-8 glow-text">Explore the Wasteland of Manga</h1>

    {{-- Filter & Search Form --}}
    <form method="GET" action="{{ route('manga.index') }}" class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 mb-6">
        <div class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search titles..." class="flex-1 p-2 bg-bg-screen border border-border rounded text-mono placeholder:opacity-50">
            <select name="category" class="p-2 bg-bg-screen border border-border rounded text-mono">
                <option value="">All Categories</option>
                @foreach(\App\Models\Category::all() as $cat)
                    <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <select name="tag" class="p-2 bg-bg-screen border border-border rounded text-mono">
                <option value="">All Tags</option>
                @foreach(\App\Models\Tag::all() as $tag)
                    <option value="{{ $tag->slug }}" {{ request('tag') == $tag->slug ? 'selected' : '' }}>
                        {{ $tag->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex space-x-2">
            <select name="sort" class="p-2 bg-bg-screen border border-border rounded text-mono">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Popular</option>
                <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Rating</option>
            </select>
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
    </form>

    {{-- Manga Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($mangas as $manga)
            <div class="manga-card panel shadow-lg hover:shadow-xl transition-all">
                <a href="{{ route('manga.show', $manga) }}" class="block">
                    <img src="{{ $manga->cover_image_url ?? asset('images/placeholder.png') }}" alt="{{ $manga->title }}" class="w-full h-64 object-cover">
                    <div class="manga-card-overlay flex flex-col">
                        <h3 class="text-future text-lg font-semibold text-glow-green mb-1">{{ $manga->title }}</h3>
                        <p class="text-sm text-text-primary opacity-80">{{ Str::limit($manga->synopsis, 60) }}</p>
                    </div>
                </a>
                <div class="flex justify-between items-center p-2 mt-2 border-t border-border">
                    <span class="badge badge-success">{{ $manga->total_chapters }} Ch.</span>
                    <span class="badge badge-primary">{{ $manga->status }}</span>
                </div>
            </div>
        @empty
            <p class="text-center text-text-primary text-xl col-span-full">No manga found.</p>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-8 flex justify-center">
        {{ $mangas->withQueryString()->links('vendor.pagination.default') }}
    </div>
</div>
@endsection
