@extends('layouts.app')

@section('title', ' • Admin: User Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">User Details - {{ $user->name }}</h1>

        <div class="space-x-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <span class="terminal-text">Back to Users</span>
            </a>
            <!-- Edit link removed -->
        </div>
    </div>

    <div class="panel p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <h2 class="text-future font-semibold text-pip-green mb-2">Avatar</h2>
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-auto rounded">
                @else
                    <p class="text-text-primary">No avatar</p>
                @endif
            </div>

            <div class="md:col-span-2">
                <h2 class="text-future font-semibold text-pip-green mb-2">Profile Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-text-primary">
                    <dt class="font-medium">Name:</dt>
                    <dd>{{ $user->name }}</dd>

                    <dt class="font-medium">Email:</dt>
                    <dd>{{ $user->email }}</dd>

                    <dt class="font-medium">Roles:</dt>
                    <dd>
                        @forelse($user->roles as $role)
                            <span class="badge badge-warning">{{ $role->name }}</span>
                        @empty
                            <span class="text-text-primary">None</span>
                        @endforelse
                    </dd>

                    <dt class="font-medium">Member Since:</dt>
                    <dd>{{ $user->created_at->format('F j, Y') }}</dd>

                    <dt class="font-medium">Last Login:</dt>
                    <dd>{{ $user->last_login_at ? $user->last_login_at->format('F j, Y H:i') : 'Never' }}</dd>

                    <dt class="font-medium">Bio:</dt>
                    <dd>{{ $user->bio ?? 'No bio' }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
