{{-- resources/views/admin/libraries/show.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Library Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">Library Details - {{ $library->getName() }}</h1>

        <div class="space-x-2">
            <a href="{{ route('admin.libraries.index') }}" class="btn btn-secondary flex items-center gap-2">
                <i class="gg-arrow-left"></i>
                <span class="terminal-text">Back to Libraries</span>
            </a>
        </div>
    </div>

    <div class="panel p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Library Info --}}
            <div class="md:col-span-2">
                <h2 class="text-future font-semibold text-pip-green mb-2">Library Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-text-primary">
                    <dt class="font-medium">Name:</dt>
                    <dd>{{ $library->getName() }}</dd>

                    <dt class="font-medium">Slug:</dt>
                    <dd>{{ $library->slug }}</dd>

                    <dt class="font-medium">Icon:</dt>
                    <dd>
                        @if($library->icon)
                            <i class="{{ $library->icon }} text-xl" style="color: {{ $library->color ?? 'inherit' }}"></i> {{ $library->icon }}
                        @else
                            <span>N/A</span>
                        @endif
                    </dd>

                    <dt class="font-medium">Color:</dt>
                    <dd>
                        @if($library->color)
                            <span class="inline-block w-6 h-6 rounded-full mr-2" style="background-color: {{ $library->color }}"></span> {{ $library->color }}
                        @else
                            <span>N/A</span>
                        @endif
                    </dd>

                    <dt class="font-medium">Status:</dt>
                    <dd>
                        @if($library->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </dd>

                    <dt class="font-medium">Access:</dt>
                    <dd>
                        @if($library->is_public)
                            <span class="badge badge-info">Public</span>
                        @else
                            <span class="badge badge-warning">Private</span>
                        @endif
                    </dd>

                    <dt class="font-medium">Sort Order:</dt>
                    <dd>{{ $library->sort_order }}</dd>

                    <dt class="font-medium">Created At:</dt>
                    <dd>{{ $library->created_at->format('F j, Y H:i') }}</dd>
                </dl>
            </div>

            {{-- Library Description --}}
            <div class="md:col-span-1">
                <h2 class="text-future font-semibold text-pip-green mb-2">Description</h2>
                <p class="text-text-primary">{{ $library->getDescription() ?? 'No description available.' }}</p>
            </div>
        </div>
    </div>

    {{-- Manga in this Library --}}
    <div class="panel p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-future font-semibold text-pip-green text-xl">Manga in this Library ({{ $library->mangas->count() }})</h2>
            <div>
                <a href="{{ route('admin.manga.create', ['library_id' => $library->id]) }}" class="btn btn-sm btn-secondary flex items-center gap-2">
                    <i class="gg-add"></i>
                    <span class="terminal-text">Add Manga</span>
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="p-3 text-future font-semibold text-pip-green">Manga Title</th>
                        <th class="p-3 text-future font-semibold text-pip-green">Source</th>
                        <th class="p-3 text-future font-semibold text-pip-green">Featured</th>
                        <th class="p-3 text-future font-semibold text-pip-green">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($library->mangas as $manga)
                        <tr class="border-b border-border hover:bg-bg-panel">
                            <td class="p-3">
                                <a href="{{ route('admin.manga.show', $manga) }}" class="text-pip-green hover:underline">
                                    {{ $manga->getTitle() }}
                                </a>
                            </td>
                            <td class="p-3">{{ $manga->source?->translations?->first()?->name ?? 'N/A' }}</td>
                            <td class="p-3 text-center">
                                @if($manga->pivot->is_featured)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-danger">No</span>
                                @endif
                            </td>
                            <td class="p-3 space-x-2">
                                <a href="{{ route('admin.manga.edit', $manga) }}" class="btn btn-secondary btn-sm inline-flex items-center gap-2">
                                    <i class="gg-edit-markup"></i>
                                    <span class="terminal-text">Edit</span>
                                </a>
                                <form action="{{ route('admin.manga.destroy', $manga) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm inline-flex items-center gap-2">
                                        <i class="gg-trash"></i>
                                        <span class="terminal-text">Remove</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-text-primary">No manga found in this library.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Library Permissions --}}
    <div class="panel p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-future font-semibold text-pip-green text-xl">Library Access Permissions</h2>
            <div class="flex gap-2">
                {{-- User Assignment Form --}}
                <form action="{{ route('admin.libraries.assign-role', [$library, 'user' => 'placeholder']) }}" method="POST" class="flex gap-2">
                    @csrf
                    <select name="user_id" class="form-select p-2 border border-border rounded text-mono" required>
                        <option value="">Select User</option>
                        @foreach($users as $user)
                            {{-- Only show users not already assigned to this library --}}
                            @if(!$library->hasManga($user->id)) {{-- Assuming hasManga can check user relationship --}}
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endif
                        @endforeach
                    </select>
                    <select name="role_id" class="form-select p-2 border border-border rounded text-mono" required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary flex items-center gap-2">
                        <i class="gg-user-add"></i>
                        <span class="terminal-text">Assign</span>
                    </button>
                </form>
            </div>
        </div>
        <div>
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="p-3 text-future font-semibold text-pip-green">User</th>
                        <th class="p-3 text-future font-semibold text-pip-green">Role</th>
                        <th class="p-3 text-future font-semibold text-pip-green">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop through users assigned to this library --}}
                    @php
                        // This part needs refinement: $library->users() relationship might not be directly available or filtered by role.
                        // We'll assume a way to get users related to the library via roles or a direct relationship.
                        // For now, we'll use a placeholder and note that this needs proper implementation.
                        $assignedUsers = $users->filter(function ($user) use ($library) {
                            // This is a placeholder logic. Actual implementation depends on how user-library relationships are stored.
                            // For example, if there's a 'library_user' pivot table with role_id.
                            return $user->roles->contains(fn($role) => $role->pivot->library_id === $library->id);
                        });
                    @endphp
                    @forelse($assignedUsers as $user)
                        @foreach($user->roles as $role)
                            {{-- Ensure the role is associated with this library --}}
                            @if($role->pivot->library_id === $library->id)
                                <tr class="border-b border-border hover:bg-bg-panel">
                                    <td class="p-3">{{ $user->name }} ({{ $user->email }})</td>
                                    <td class="p-3">
                                        <span class="badge badge-{{ $role->level >= 50 ? 'primary' : 'secondary' }}">
                                            {{ $role->name }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <form action="{{ route('admin.libraries.remove-role', [$library, 'user' => $user, 'role' => $role]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm inline-flex items-center gap-2">
                                                <i class="gg-user-remove"></i>
                                                <span class="terminal-text">Remove Role</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="3" class="p-4 text-center text-text-primary">No users have been granted access to this library yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
