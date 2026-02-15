{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-future text-4xl font-bold mb-6 glow-text">MANGON ADMIN PANEL</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Manga Count --}}
        <div class="panel p-4 flex items-center justify-between">
            <div>
                <h2 class="text-future font-semibold text-2xl mb-1 glow-text">Total Manga</h2>
                <p class="text-4xl font-bold text-pip-green">{{ \App\Models\Manga::count() }}</p>
            </div>
            <div class="text-pip-green">
                <svg class="h-16 w-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </div>
        </div>

        {{-- Chapters Count --}}
        <div class="panel p-4 flex items-center justify-between">
            <div>
                <h2 class="text-future font-semibold text-2xl mb-1 glow-text">Total Chapters</h2>
                <p class="text-4xl font-bold text-pip-green">{{ \App\Models\Chapter::count() }}</p>
            </div>
            <div class="text-pip-green">
                <svg class="h-16 w-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </div>
        </div>

        {{-- Users Count --}}
        <div class="panel p-4 flex items-center justify-between">
            <div>
                <h2 class="text-future font-semibold text-2xl mb-1 glow-text">Total Users</h2>
                <p class="text-4xl font-bold text-pip-green">{{ \App\Models\User::count() }}</p>
            </div>
            <div class="text-pip-green">
                <svg class="h-16 w-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2a7 7 0 017 7v5a5 5 0 00-5 5h-4a5 5 0 00-5-5v-5a7 7 0 017-7z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="{{ route('admin.manga.index') }}" class="panel p-4 flex flex-col items-center justify-center hover:shadow-xl transition-all">
            <svg class="h-12 w-12 text-pip-green mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3h18v18H3z" />
            </svg>
            <span class="text-future text-xl font-semibold">Manga Management</span>
        </a>

        <a href="{{ route('admin.chapters.index') }}" class="panel p-4 flex flex-col items-center justify-center hover:shadow-xl transition-all">
            <svg class="h-12 w-12 text-pip-green mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3h18v18H3z" />
            </svg>
            <span class="text-future text-xl font-semibold">Chapters Management</span>
        </a>

        <a href="{{ route('admin.users.index') }}" class="panel p-4 flex flex-col items-center justify-center hover:shadow-xl transition-all">
            <svg class="h-12 w-12 text-pip-green mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3h18v18H3z" />
            </svg>
            <span class="text-future text-xl font-semibold">Users Management</span>
        </a>

        <a href="{{ route('admin.settings') }}" class="panel p-4 flex flex-col items-center justify-center hover:shadow-xl transition-all">
            <svg class="h-12 w-12 text-pip-green mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3h18v18H3z" />
            </svg>
            <span class="text-future text-xl font-semibold">Settings</span>
        </a>
    </div>
</div>
@endsection
