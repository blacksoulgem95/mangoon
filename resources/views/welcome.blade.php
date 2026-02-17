@extends('layouts.app')

@section('title', ' - Welcome to the Wasteland')

@section('content')
<div class="container mx-auto px-4 py-12">
    <!-- Hero Section -->
    <div class="text-center mb-16 relative">
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-pip-green rounded-full filter blur-[100px] opacity-20 pointer-events-none"></div>

        <h1 class="text-6xl md:text-8xl font-bold text-future text-pip-green glow-text mb-4 tracking-tighter">
            MANGOON
        </h1>
        <p class="text-xl md:text-2xl text-radiation-yellow font-mono uppercase tracking-widest mb-8">
            MANGA + GOON. DON'T ASK.
        </p>

        <div class="max-w-2xl mx-auto border-l-4 border-pip-green pl-6 py-2 text-left bg-bg-panel/50 backdrop-blur-sm">
            <p class="text-text-primary text-lg leading-relaxed font-mono">
                <span class="text-pip-green font-bold">> SYSTEM_MESSAGE:</span>
                Welcome to the digital wasteland. You are here because paper is fragile and reality is boring.
                This is a retro-futuristic archive for your Japanese graphic novel obsessions.
                Interpret the name however you want; we don't judge (much).
            </p>
        </div>

        <div class="mt-10 flex justify-center gap-4">
            <a href="{{ route('manga.index') }}" class="btn btn-primary text-lg px-8 py-3 flex items-center gap-2 group">
                <i class="gg-eye group-hover:scale-110 transition-transform"></i>
                <span class="terminal-text">INITIATE BROWSING</span>
            </a>
            @guest
                <a href="{{ route('login') }}" class="btn btn-secondary text-lg px-8 py-3 flex items-center gap-2 group">
                    <i class="gg-key group-hover:scale-110 transition-transform"></i>
                    <span class="terminal-text">ACCESS TERMINAL</span>
                </a>
            @endguest
        </div>
    </div>

    <!-- Features Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
        <!-- Feature 1 -->
        <div class="panel p-6 hover:shadow-[0_0_15px_rgba(51,255,51,0.3)] transition-shadow duration-300 group">
            <div class="text-radiation-yellow mb-4 flex justify-center">
                <i class="gg-box" style="--ggs: 2;"></i>
            </div>
            <h3 class="text-xl font-bold text-center text-pip-green font-future mb-2">CBZ SUPREMACY</h3>
            <p class="text-center text-sm text-text-primary opacity-80">
                We respect the sacred .cbz format. Zipped images are the future, even though they're technically from the past.
            </p>
        </div>

        <!-- Feature 2 -->
        <div class="panel p-6 hover:shadow-[0_0_15px_rgba(51,255,51,0.3)] transition-shadow duration-300 group">
            <div class="text-radiation-yellow mb-4 flex justify-center">
                <i class="gg-user-list" style="--ggs: 2;"></i>
            </div>
            <h3 class="text-xl font-bold text-center text-pip-green font-future mb-2">CLASS WARFARE</h3>
            <p class="text-center text-sm text-text-primary opacity-80">
                Admins, Editors, Moderators, Readers. A strict hierarchy to remind you of your place in the digital ecosystem.
            </p>
        </div>

        <!-- Feature 3 -->
        <div class="panel p-6 hover:shadow-[0_0_15px_rgba(51,255,51,0.3)] transition-shadow duration-300 group">
            <div class="text-radiation-yellow mb-4 flex justify-center">
                <i class="gg-terminal" style="--ggs: 2;"></i>
            </div>
            <h3 class="text-xl font-bold text-center text-pip-green font-future mb-2">TERMINAL VIBES</h3>
            <p class="text-center text-sm text-text-primary opacity-80">
                Green text on black backgrounds. If it looks like you're hacking a mainframe while reading slice-of-life, we've succeeded.
            </p>
        </div>

        <!-- Feature 4 -->
        <div class="panel p-6 hover:shadow-[0_0_15px_rgba(51,255,51,0.3)] transition-shadow duration-300 group">
            <div class="text-radiation-yellow mb-4 flex justify-center">
                <i class="gg-database" style="--ggs: 2;"></i>
            </div>
            <h3 class="text-xl font-bold text-center text-pip-green font-future mb-2">DATA HOARDING</h3>
            <p class="text-center text-sm text-text-primary opacity-80">
                Metadata tracking for everything. Because knowing exactly how much time you've wasted is half the fun.
            </p>
        </div>
    </div>

    <!-- Status Output -->
    <div class="max-w-3xl mx-auto panel p-4 font-mono text-sm">
        <div class="flex items-center gap-2 mb-2 border-b border-border pb-2">
            <i class="gg-server text-pip-green"></i>
            <span class="text-radiation-yellow">SYSTEM_STATUS_LOG</span>
        </div>
        <div class="space-y-1 text-xs md:text-sm">
            <div class="flex justify-between">
                <span class="text-text-primary">> SYSTEM_CORE</span>
                <span class="text-pip-green">[ONLINE]</span>
            </div>
            <div class="flex justify-between">
                <span class="text-text-primary">> DATABASE_CONNECTION</span>
                <span class="text-pip-green">[ESTABLISHED]</span>
            </div>
            <div class="flex justify-between">
                <span class="text-text-primary">> USER_JUDGMENT_MODULE</span>
                <span class="text-radiation-yellow">[ACTIVE - WATCHING YOU]</span>
            </div>
            <div class="flex justify-between">
                <span class="text-text-primary">> WAIFU_INTEGRITY_CHECK</span>
                <span class="text-red-500">[FAILED - CRITICAL LEVELS OF CRINGE DETECTED]</span>
            </div>
            <div class="mt-4 pt-2 border-t border-border text-center text-text-primary opacity-50">
                // END OF LINE
            </div>
        </div>
    </div>
</div>
@endsection
