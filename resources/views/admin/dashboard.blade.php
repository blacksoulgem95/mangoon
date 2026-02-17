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
            <div class="text-pip-green flex justify-center">
                <i class="gg-layout-grid" style="--ggs: 3;"></i>
            </div>
        </div>

        {{-- Chapters Count --}}
        <div class="panel p-4 flex items-center justify-between">
            <div>
                <h2 class="text-future font-semibold text-2xl mb-1 glow-text">Total Chapters</h2>
                <p class="text-4xl font-bold text-pip-green">{{ \App\Models\Chapter::count() }}</p>
            </div>
            <div class="text-pip-green flex justify-center">
                <i class="gg-file-document" style="--ggs: 3;"></i>
            </div>
        </div>

        {{-- Users Count --}}
        <div class="panel p-4 flex items-center justify-between">
            <div>
                <h2 class="text-future font-semibold text-2xl mb-1 glow-text">Total Users</h2>
                <p class="text-4xl font-bold text-pip-green">{{ \App\Models\User::count() }}</p>
            </div>
            <div class="text-pip-green flex justify-center">
                <i class="gg-user" style="--ggs: 3;"></i>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="{{ route('admin.manga.index') }}" class="panel p-4 flex flex-col items-center justify-center hover:shadow-xl transition-all">
            <div class="mb-4 text-pip-green"><i class="gg-layout-grid" style="--ggs: 2;"></i></div>
            <span class="text-future text-xl font-semibold">Manga Management</span>
        </a>

        <a href="{{ route('admin.chapters.index') }}" class="panel p-4 flex flex-col items-center justify-center hover:shadow-xl transition-all">
            <div class="mb-4 text-pip-green"><i class="gg-file-document" style="--ggs: 2;"></i></div>
            <span class="text-future text-xl font-semibold">Chapters Management</span>
        </a>

        <a href="{{ route('admin.users.index') }}" class="panel p-4 flex flex-col items-center justify-center hover:shadow-xl transition-all">
            <div class="mb-4 text-pip-green"><i class="gg-user-list" style="--ggs: 2;"></i></div>
            <span class="text-future text-xl font-semibold">Users Management</span>
        </a>

        <a href="{{ route('admin.settings') }}" class="panel p-4 flex flex-col items-center justify-center hover:shadow-xl transition-all">
            <div class="mb-4 text-pip-green"><i class="gg-options" style="--ggs: 2;"></i></div>
            <span class="text-future text-xl font-semibold">Settings</span>
        </a>
    </div>
</div>
@endsection
