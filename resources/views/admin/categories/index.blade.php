{{-- resources/views/admin/categories/index.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Categories List')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">Categories Management</h1>

        <div class="space-x-2 flex">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary flex items-center gap-2">
                <i class="gg-layout-grid"></i>
                <span class="terminal-text">Dashboard</span>
            </a>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary flex items-center gap-2">
                <i class="gg-add"></i>
                <span class="terminal-text">New Category</span>
            </a>
        </div>
    </div>

    {{-- Search & Filters --}}
    <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or slug…" class="p-2 bg-bg-screen border border-border rounded text-mono w-full sm:w-64">

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

    {{-- Categories Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr>
                    <th class="p-3 text-future font-semibold text-pip-green">Name</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Slug</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Parent</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Sort Order</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Active</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr class="border-b border-border hover:bg-bg-panel">
                        <td class="p-3">
                            {{ $category->getName() }}
                        </td>
                        <td class="p-3">{{ $category->slug }}</td>
                        <td class="p-3">{{ $category->parent ? $category->parent->getName() : 'N/A' }}</td>
                        <td class="p-3 text-center">{{ $category->sort_order }}</td>
                        <td class="p-3 text-center">
                            @if($category->is_active)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-danger">No</span>
                            @endif
                        </td>
                        <td class="p-3 space-x-2">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-secondary btn-sm inline-flex items-center gap-2">
                                <i class="gg-edit-markup"></i>
                                <span class="terminal-text">Edit</span>
                            </a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category and all its children?');" class="inline">
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
                        <td colspan="6" class="p-4 text-center text-text-primary">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}\
    <div class="mt-6 flex justify-center">
        {{ $categories->withQueryString()->links('vendor.pagination.default') }}
    </div>
</div>
@endsection
