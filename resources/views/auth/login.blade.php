@extends('layouts.app')

@section('title', ' - Login')

@section('content')
<div class="flex items-center justify-center min-h-[calc(100vh-200px)]">
    <div class="w-full max-w-md">
        <div class="bg-bg-panel border-2 border-pip-green shadow-[0_0_15px_rgba(51,255,51,0.2)] p-8 relative overflow-hidden">
            <!-- Decorative corner markers -->
            <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-radiation-yellow"></div>
            <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-radiation-yellow"></div>
            <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-radiation-yellow"></div>
            <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-radiation-yellow"></div>

            <h2 class="text-3xl font-bold text-center text-future text-pip-green mb-8 tracking-wider">
                <span class="animate-pulse">█</span> ACCESS TERMINAL
            </h2>

            <form method="POST" action="{{ route('authenticate') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-6">
                    <label for="email" class="block text-radiation-yellow text-sm font-bold mb-2 uppercase tracking-wide">
                        Identity Code (Email)
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full bg-bg-screen border-2 border-pip-green text-pip-green p-3 focus:outline-none focus:shadow-[0_0_10px_rgba(51,255,51,0.4)] placeholder-pip-green/30 font-mono"
                        placeholder="USER@MANGOON.SYS">
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-8">
                    <label for="password" class="block text-radiation-yellow text-sm font-bold mb-2 uppercase tracking-wide">
                        Access Key (Password)
                    </label>
                    <input id="password" type="password" name="password" required
                        class="w-full bg-bg-screen border-2 border-pip-green text-pip-green p-3 focus:outline-none focus:shadow-[0_0_10px_rgba(51,255,51,0.4)] placeholder-pip-green/30 font-mono"
                        placeholder="••••••••">
                    @error('password')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-pip-green bg-bg-screen border-pip-green focus:ring-pip-green">
                        <label for="remember_me" class="ml-2 text-sm text-pip-green font-mono">
                            REMEMBER SESSION
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <a class="text-sm text-radiation-yellow hover:text-pip-green hover:underline font-mono" href="{{ route('password.request') }}">
                            LOST KEY?
                        </a>
                    @endif
                </div>

                <div class="mt-8">
                    <button type="submit" class="w-full bg-pip-green hover:bg-radiation-yellow text-bg-screen font-bold py-3 px-4 uppercase tracking-widest transition-colors duration-300 font-future clip-path-polygon">
                        Initiate Sequence
                    </button>
                </div>
            </form>
        </div>

        <div class="text-center mt-4 font-mono text-xs text-pip-green/50">
            SYSTEM VERSION 2.0.77 // SECURE CONNECTION ESTABLISHED
        </div>
    </div>
</div>
@endsection
