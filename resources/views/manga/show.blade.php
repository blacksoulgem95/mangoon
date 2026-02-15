{{-- resources/views/manga/show.blade.php --}}
@extends('layouts.app')

@section('title', ' • ' . $manga->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-6">
        {{-- Manga Cover --}}
        <div class="flex-shrink-0 md:w-1/3">
            <div class="panel">
                <img src="{{ $manga->cover_image_url ?? asset('images/placeholder.png') }}" alt="{{ $manga->title }}" class="w-full h-auto object-cover">
                @if($manga->banner_image_url)
                    <img src="{{ $manga->banner_image_url }}" alt="{{ $manga->title }} banner" class="mt-4 w-full h-auto object-cover">
                @endif
                @if($manga->is_featured)
                    <span class="badge badge-primary absolute top-2 left-2">Featured</span>
                @endif
            </div>
        </div>

        {{-- Manga Details --}}
        <div class="md:w-2/3">
            <h1 class="text-future text-3xl font-bold mb-4 glow-text">{{ $manga->title }}</h1>
            <p class="text-text-primary mb-4">{{ $manga->synopsis }}</p>

            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($manga->tags as $tag)
                    <span class="badge badge-success">{{ $tag->name }}</span>
                @endforeach
                @foreach($manga->categories as $cat)
                    <span class="badge badge-warning">{{ $cat->name }}</span>
                @endforeach
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-text-primary">
                <dt class="font-semibold">Author:</dt>
                <dd>{{ $manga->author ?? 'Unknown' }}</dd>

                <dt class="font-semibold">Illustrator:</dt>
                <dd>{{ $manga->illustrator ?? 'Unknown' }}</dd>

                <dt class="font-semibold">Publisher:</dt>
                <dd>{{ $manga->publisher ?? 'Unknown' }}</dd>

                <dt class="font-semibold">Status:</dt>
                <dd>{{ ucfirst($manga->status) }}</dd>

                <dt class="font-semibold">Rating:</dt>
                <dd>{{ number_format($manga->rating ?? 0, 2) }} / 10</dd>

                <dt class="font-semibold">Chapters:</dt>
                <dd>{{ $manga->chapters->count() }}</dd>
            </dl>

            <div class="mt-6">
                <h2 class="text-future text-2xl font-semibold mb-2">Chapters</h2>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($manga->chapters as $chapter)
                        <li>
                            <a href="{{ route('chapter.show', [$manga, $chapter]) }}" class="text-pip-green hover:underline">
                                {{ $chapter->displayName() }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Back to List --}}
    <div class="mt-8">
        <a href="{{ route('manga.index') }}" class="btn btn-secondary">
            &larr; Back to Manga List
        </a>
    </div>
</div>
@endsection
