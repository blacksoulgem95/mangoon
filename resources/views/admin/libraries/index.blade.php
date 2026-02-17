{{-- resources/views/admin/libraries/index.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Libraries List')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">Libraries Management</h1>

        <div class="space-x-2 flex">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary flex items-center gap-2">
                <i class="gg-layout-grid"></i>
                <span class="terminal-text">Dashboard</span>
            </a>
            <a href="{{ route('admin.libraries.create') }}" class="btn btn-primary flex items-center gap-2">
                <i class="gg-add"></i>
                <span class="terminal-text">New Library</span>
            </a>
        </div>
    </div>

    {{-- Search & Filters --}}
    <form method="GET" action="{{ route('admin.libraries.index') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or slug…" class="p-2 bg-bg-screen border border-border rounded text-mono w-full sm:w-64">

        <select name="is_active" class="p-2 bg-bg-screen border border-border rounded text-mono">
            <option value="">All Statuses</option>
            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>

        <select name="is_public" class="p-2 bg-bg-screen border border-border rounded text-mono">
            <option value="">All Access</option>
            <option value="1" {{ request('is_public') === '1' ? 'selected' : '' }}>Public</option>
            <option value="0" {{ request('is_public') === '0' ? 'selected' : '' }}>Private</option>
        </select>

        <select name="sort_by" class="p-2 bg-bg-screen border border-border rounded text-mono">
            <option value="sort_order" {{ request('sort_by') == 'sort_order' ? 'selected' : '' }}>Sort Order</option>
            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Created At</option>
            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
        </select>

        <select name="sort_direction" class="p-2 bg-bg-screen border border-border rounded text-mono">
            <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>Asc</option>
            <option value="desc" {{ request('sort_direction') == 'desc' ? 'selected' : '' }}>Desc</option>
        </select>

        <button type="submit" class="btn btn-secondary flex items-center gap-2">
            <i class="gg-search"></i>
            <span class="terminal-text">Apply</span>
        </button>
    </form>

    {{-- Libraries Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr>
                    <th class="p-3 text-future font-semibold text-pip-green">Icon</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Name</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Slug</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Access</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Status</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Sort Order</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($libraries as $library)
                    <tr class="border-b border-border hover:bg-bg-panel">
                        <td class="p-3 text-center">
                            @if($library->icon)
                                <i class="{{ $library->icon }} text-xl" style="color: {{ $library->color ?? 'inherit' }}"></i>
                            @else
                                <span class="text-text-primary">N/A</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <a href="{{ route('admin.libraries.show', $library) }}" class="text-pip-green hover:underline">
                                {{ $library->getName() }}
                            </a>
                        </td>
                        <td class="p-3">{{ $library->slug }}</td>
                        <td class="p-3 text-center">
                            @if($library->is_public)
                                <span class="badge badge-success">Public</span>
                            @else
                                <span class="badge badge-danger">Private</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            @if($library->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="p-3 text-center">{{ $library->sort_order }}</td>
                        <td class="p-3 space-x-2">
                            <a href="{{ route('admin.libraries.show', $library) }}" class="btn btn-primary btn-sm inline-flex items-center gap-2">
                                <i class="gg-eye"></i>
                                <span class="terminal-text">View</span>
                            </a>
                            <a href="{{ route('admin.libraries.edit', $library) }}" class="btn btn-secondary btn-sm inline-flex items-center gap-2">
                                <i class="gg-edit-markup"></i>
                                <span class="terminal-text">Edit</span>
                            </a>
                            <form action="{{ route('admin.libraries.destroy', $library) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this library? This will also remove all associated mangas from this library, but not the mangas themselves.');" class="inline">
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
                        <td colspan="7" class="p-4 text-center text-text-primary">No libraries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6 flex justify-center">
        {{ $libraries->withQueryString()->links('vendor.pagination.default') }}
    </div>
</div>
@endsection
