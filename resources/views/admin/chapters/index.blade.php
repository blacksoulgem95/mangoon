{{-- resources/views/admin/chapters/index.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: All Chapters')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">All Chapters</h1>

        <div class="space-x-2 flex">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary flex items-center gap-2">
                <i class="gg-layout-grid"></i>
                <span class="terminal-text">Dashboard</span>
            </a>
            <a href="{{ route('admin.chapters.index') }}" class="btn btn-primary flex items-center gap-2">
                <i class="gg-sync"></i>
                <span class="terminal-text">Refresh</span>
            </a>
        </div>
    </div>

    {{-- Search & Filters --}}
    <form method="GET" action="{{ route('admin.chapters.index') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search…" class="p-2 bg-bg-screen border border-border rounded text-mono w-full sm:w-64">

        <select name="is_active" class="p-2 bg-bg-screen border border-border rounded text-mono">
            <option value="">All Activity</option>
            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>

        <button type="submit" class="btn btn-secondary flex items-center gap-2">
            <i class="gg-search"></i>
            <span class="terminal-text">Apply</span>
        </button>
    </form>

    {{-- Chapters Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr>
                    <th class="p-3 text-future font-semibold text-pip-green">Cover</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Title</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Manga</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Chapter No.</th>
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
                            <a href="{{ route('admin.chapters.show', $chapter) }}" class="text-pip-green hover:underline">
                                {{ $chapter->displayName() }}
                            </a>
                        </td>
                        <td class="p-3">
                            <a href="{{ route('admin.manga.show', $chapter->manga) }}" class="text-pip-green hover:underline">
                                {{ $chapter->manga->title }}
                            </a>
                        </td>
                        <td class="p-3">{{ $chapter->chapter_number }}</td>
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
                            <a href="{{ route('admin.chapters.show', $chapter) }}" class="btn btn-secondary btn-sm inline-flex items-center gap-2">
                                <i class="gg-eye"></i>
                                <span class="terminal-text">View</span>
                            </a>
                            <form action="{{ route('admin.manga.chapters.toggle-active', [$chapter->manga, $chapter]) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $chapter->is_active ? 'btn-danger' : 'btn-primary' }} inline-flex items-center gap-2">
                                    <i class="{{ $chapter->is_active ? 'gg-toggle-off' : 'gg-toggle-on' }}"></i>
                                    <span class="terminal-text">{{ $chapter->is_active ? 'Deactivate' : 'Activate' }}</span>
                                </button>
                            </form>
                            <a href="{{ route('admin.manga.chapters.edit', [$chapter->manga, $chapter]) }}" class="btn btn-secondary btn-sm inline-flex items-center gap-2">
                                <i class="gg-pen"></i>
                                <span class="terminal-text">Edit</span>
                            </a>
                            <form action="{{ route('admin.manga.chapters.destroy', [$chapter->manga, $chapter]) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this chapter?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm inline-flex items-center gap-2">
                                    <i class="gg-trash"></i>
                                    <span class="terminal-text">Delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-4 text-center text-text-primary">No chapters found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6 flex justify-center">
        {{ $chapters->withQueryString()->links('vendor.pagination.default') }}
    </div>
</div>
@endsection
