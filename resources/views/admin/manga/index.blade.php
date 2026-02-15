{{-- resources/views/admin/manga/index.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Manga List')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">Manga Management</h1>
        <a href="{{ route('admin.manga.create') }}" class="btn btn-primary">
            <span class="terminal-text">Add New Manga</span>
        </a>
    </div>

    {{-- Search & Filters --}}
    <form method="GET" action="{{ route('admin.manga.index') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search…" class="p-2 bg-bg-screen border border-border rounded text-mono w-full sm:w-64">
        <select name="status" class="p-2 bg-bg-screen border border-border rounded text-mono">
            <option value="">All Statuses</option>
            <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="hiatus" {{ request('status') == 'hiatus' ? 'selected' : '' }}>Hiatus</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
        </select>
        <select name="is_active" class="p-2 bg-bg-screen border border-border rounded text-mono">
            <option value="">All Activity</option>
            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="btn btn-secondary">
            <span class="terminal-text">Apply</span>
        </button>
    </form>

    {{-- Manga Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr>
                    <th class="p-3 text-future font-semibold text-pip-green">Cover</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Title</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Author</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Status</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Chapters</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Featured</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Active</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mangas as $manga)
                    <tr class="border-b border-border hover:bg-bg-panel">
                        <td class="p-3 text-center">
                            <img src="{{ $manga->cover_image_url ?? asset('images/placeholder.png') }}" alt="{{ $manga->title }}" class="w-12 h-16 object-cover mx-auto rounded">
                        </td>
                        <td class="p-3">
                            <a href="{{ route('admin.manga.show', $manga) }}" class="text-pip-green hover:underline">
                                {{ $manga->title }}
                            </a>
                        </td>
                        <td class="p-3">{{ $manga->author ?? '-' }}</td>
                        <td class="p-3">{{ ucfirst($manga->status) }}</td>
                        <td class="p-3">{{ $manga->chapters->count() }}</td>
                        <td class="p-3 text-center">
                            @if($manga->is_featured)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-danger">No</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            @if($manga->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="p-3 space-x-2">
                            <a href="{{ route('admin.manga.edit', $manga) }}" class="btn btn-secondary btn-sm">
                                <span class="terminal-text">Edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.manga.destroy', $manga) }}" class="inline-block" onsubmit="return confirm('Delete this manga?');">
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
                        <td colspan="8" class="p-4 text-center text-text-primary">No manga found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6 flex justify-center">
        {{ $mangas->withQueryString()->links('vendor.pagination.default') }}
    </div>
</div>
@endsection
