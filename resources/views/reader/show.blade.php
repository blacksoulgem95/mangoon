@extends('layouts.app')

@section('title', "- Reading {$chapter->getDisplayName()}")

@push('styles')
<style>
    /* Reader-specific styles */
    body {
        background-color: #000;
    }

    .reader-wrapper {
        background-color: #000;
        min-height: 100vh;
    }

    .reader-header {
        background: linear-gradient(180deg, rgba(10, 14, 15, 0.98), transparent);
        border-bottom: 2px solid var(--color-border);
    }

    .reader-content {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 160px);
        padding: 2rem 0;
    }

    .reader-page-container {
        position: relative;
        max-width: 100%;
        cursor: pointer;
    }

    .reader-page-image {
        max-width: 100%;
        max-height: 90vh;
        width: auto;
        height: auto;
        display: block;
        margin: 0 auto;
        box-shadow: 0 0 50px rgba(0, 255, 65, 0.3);
        border: 2px solid var(--color-border);
    }

    .reader-nav-overlay {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 30%;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .reader-nav-overlay:hover {
        opacity: 1;
        background: linear-gradient(90deg, rgba(0, 255, 65, 0.1), transparent);
    }

    .reader-nav-overlay.nav-left {
        left: 0;
    }

    .reader-nav-overlay.nav-right {
        right: 0;
        background: linear-gradient(-90deg, rgba(0, 255, 65, 0.1), transparent);
    }

    .reader-controls-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(10, 14, 15, 0.98), rgba(10, 14, 15, 0.9));
        border-top: 3px solid var(--color-border);
        padding: 1.5rem;
        z-index: 100;
        transform: translateY(100%);
        transition: transform 0.3s ease;
        box-shadow: 0 -4px 20px rgba(0, 255, 65, 0.2);
    }

    .reader-controls-bar.visible {
        transform: translateY(0);
    }

    .reader-wrapper:hover .reader-controls-bar {
        transform: translateY(0);
    }

    .page-slider {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 8px;
        background: rgba(42, 74, 58, 0.5);
        border-radius: 4px;
        outline: none;
        border: 2px solid var(--color-border);
    }

    .page-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 24px;
        height: 24px;
        background: var(--color-primary);
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid var(--color-border);
        box-shadow: 0 0 15px rgba(0, 255, 65, 0.8);
    }

    .page-slider::-moz-range-thumb {
        width: 24px;
        height: 24px;
        background: var(--color-primary);
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid var(--color-border);
        box-shadow: 0 0 15px rgba(0, 255, 65, 0.8);
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .settings-panel {
        position: fixed;
        right: 0;
        top: 0;
        bottom: 0;
        width: 320px;
        background: rgba(26, 31, 31, 0.98);
        border-left: 3px solid var(--color-border);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        z-index: 200;
        overflow-y: auto;
        box-shadow: -4px 0 20px rgba(0, 255, 65, 0.2);
    }

    .settings-panel.visible {
        transform: translateX(0);
    }
</style>
@endpush

@section('content')
<div class="reader-wrapper" id="readerWrapper">
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner mb-6"></div>
        <p class="terminal-text text-2xl text-pip-green">LOADING CHAPTER DATA...</p>
        <p class="text-mono text-sm mt-2 text-text-primary opacity-70">Please wait while we prepare your reading experience</p>
    </div>

    <!-- Reader Header -->
    <div class="reader-header fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Back Button -->
                <a href="{{ route('manga.show', $manga->slug) }}" class="btn btn-secondary">
                    ← BACK TO MANGA
                </a>

                <!-- Chapter Info -->
                <div class="text-center flex-1 mx-4">
                    <h2 class="text-xl md:text-2xl text-future truncate">{{ $manga->getTitle() }}</h2>
                    <p class="text-sm terminal-text text-radiation-yellow mt-1">{{ $chapter->getDisplayName() }}</p>
                </div>

                <!-- Settings Button -->
                <button id="settingsBtn" class="btn">
                    ⚙ SETTINGS
                </button>
            </div>
        </div>
    </div>

    <!-- Main Reader Content -->
    <div class="reader-content pt-24" id="readerContent">
        <div class="reader-page-container" id="pageContainer">
            <!-- Page navigation overlays -->
            <div class="reader-nav-overlay nav-left" id="navPrev" title="Previous Page"></div>
            <div class="reader-nav-overlay nav-right" id="navNext" title="Next Page"></div>
            
            <!-- The manga page image -->
            <img id="currentPage" class="reader-page-image" alt="Manga Page" />
        </div>
    </div>

    <!-- Reader Controls Bar -->
    <div class="reader-controls-bar" id="controlsBar">
        <div class="max-w-6xl mx-auto">
            <!-- Page Progress -->
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="terminal-text text-sm">
                        PAGE: <span id="currentPageNum" class="text-pip-green">1</span> / <span id="totalPages" class="text-radiation-yellow">{{ $pageCount }}</span>
                    </span>
                    <span class="terminal-text text-sm">
                        PROGRESS: <span id="progressPercent" class="text-pip-green">0</span>%
                    </span>
                </div>
                <input type="range" id="pageSlider" class="page-slider" min="0" max="{{ $pageCount - 1 }}" value="0" />
            </div>

            <!-- Navigation Buttons -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <!-- Chapter Navigation -->
                @if($previousChapter)
                    <a href="{{ route('chapter.show', [$manga->slug, $previousChapter->slug]) }}" class="btn btn-secondary w-full">
                        ⏮ PREV CHAPTER
                    </a>
                @else
                    <button class="btn opacity-50 cursor-not-allowed w-full" disabled>
                        ⏮ PREV CHAPTER
                    </button>
                @endif

                <!-- Page Navigation -->
                <button id="btnPrevPage" class="btn w-full">
                    ← PREV PAGE
                </button>
                <button id="btnNextPage" class="btn w-full">
                    NEXT PAGE →
                </button>

                @if($nextChapter)
                    <a href="{{ route('chapter.show', [$manga->slug, $nextChapter->slug]) }}" class="btn btn-secondary w-full">
                        NEXT CHAPTER ⏭
                    </a>
                @else
                    <button class="btn opacity-50 cursor-not-allowed w-full" disabled>
                        NEXT CHAPTER ⏭
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Settings Panel -->
    <div class="settings-panel" id="settingsPanel">
        <div class="p-6">
            <!-- Close Button -->
            <div class="flex items-center justify-between mb-6 pb-4 border-b-2 border-border">
                <h3 class="text-xl text