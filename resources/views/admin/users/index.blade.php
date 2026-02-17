{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Users List')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">Users Management</h1>

        <div class="space-x-2 flex">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary flex items-center gap-2">
                <i class="gg-layout-grid"></i>
                <span class="terminal-text">Dashboard</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary flex items-center gap-2">
                <i class="gg-sync"></i>
                <span class="terminal-text">Refresh</span>
            </a>
        </div>
    </div>

    {{-- Search & Filters --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email…" class="p-2 bg-bg-screen border border-border rounded text-mono w-full sm:w-64">

        <button type="submit" class="btn btn-secondary flex items-center gap-2">
            <i class="gg-search"></i>
            <span class="terminal-text">Apply</span>
        </button>
    </form>

    {{-- Users Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr>
                    <th class="p-3 text-future font-semibold text-pip-green">Avatar</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Name</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Email</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Roles</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Joined</th>
                    <th class="p-3 text-future font-semibold text-pip-green">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-b border-border hover:bg-bg-panel">
                        <td class="p-3 text-center">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-12 h-12 rounded-full object-cover">
                            @else
                                <span class="text-text-primary">N/A</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-pip-green hover:underline">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td class="p-3">{{ $user->email }}</td>
                        <td class="p-3">
                            @forelse($user->roles as $role)
                                <span class="badge badge-warning">{{ $role->name }}</span>
                            @empty
                                <span class="text-text-primary">None</span>
                            @endforelse
                        </td>
                        <td class="p-3">{{ $user->created_at->format('F j, Y') }}</td>
                        <td class="p-3 space-x-2">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary btn-sm inline-flex items-center gap-2">
                                <i class="gg-profile"></i>
                                <span class="terminal-text">View</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-text-primary">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6 flex justify-center">
        {{ $users->withQueryString()->links('vendor.pagination.default') }}
    </div>
</div>
@endsection
