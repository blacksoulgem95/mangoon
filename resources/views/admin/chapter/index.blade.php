{{-- resources/views/admin/chapter/index.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Chapters List')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">Chapters for "{{ $manga->title }}"</h1>
        <div class="space-x-2">
            <a href="{{ route('admin.manga.chapters.create', $manga) }}" class="btn btn-primary">
                <span class="terminal-text">Add New Chapter</span>
            </a>
            <a href="{{ route('admin.manga.index') }}" class="btn btn-secondary">
                <span class="terminal-text">Back to Manga List</span>
            </a>
        </div>
    </div>

    {{-- Chapters Table --}}
    <div class="panel p-6">
        <table class="w-full">
            <thead>
                <tr>
                    <th class="p-3 text-future font-semibold text-pip-green">Cover</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Title</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Volume</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Sort Order</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Status</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($chapters as $chapter)
                    <tr class="border-b border-border hover:bg-bg-panel">
                        <td class="p-3 text-center">
                            @if($chapter->cover_image_url)
                                <img src="{{ $chapter->cover_image_url }}" alt="{{ $chapter->title }}" class="w-12 h-16 object-cover rounded">
                            @else
                                <span class="text-text-primary">N/A</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <a href="{{ route('admin.manga.chapters.edit', [$manga, $chapter]) }}" class="text-pip-green hover:underline">
                                {{ $chapter->displayName() }}
                            </a>
                        </td>
                        <td class="p-3">{{ $chapter->volume_number ?? '-' }}</td>
                        <td class="p-3">{{ $chapter->sort_order }}</td>
                        <td class="p-3">
                            @if($chapter->is_premium)
                                <span class="badge badge-danger">Premium</span>
                            @else
                                <span class="badge badge-success">Free</span>
                            @endif
                        </td>
                        <td class="p-3 space-x-2">
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
                        <td colspan="6" class="p-4 text-center text-text-primary">No chapters found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="mt-6 flex justify-center">
            {{ $chapters->withQueryString()->links('vendor.pagination.default') }}
        </div>
    </div>
</div>
@endsection
