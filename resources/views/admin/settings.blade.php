{{-- resources/views/admin/settings.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green flex items-center gap-3">
            <i class="gg-options" style="--ggs: 1.2;"></i> Admin Settings
        </h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary flex items-center gap-2">
            <i class="gg-layout-grid"></i>
            <span class="terminal-text">Dashboard</span>
        </a>
    </div>

    <div class="panel p-6">
        <h2 class="text-future font-semibold text-pip-green mb-4 flex items-center gap-2">
            <i class="gg-sliders"></i> General Settings
        </h2>
        <div class="flex items-center gap-3 text-text-primary p-4 border border-pip-green/30 bg-pip-green/5 rounded">
            <i class="gg-info"></i>
            <p>Settings management is under construction. Please check back later.</p>
        </div>
    </div>
</div>
@endsection
