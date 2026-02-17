<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Mangoon') }} @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href='https://css.gg/css' rel='stylesheet'>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="antialiased">
    <!-- Main Navigation -->
    <nav class="sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo / Brand -->
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3">
                        <div class="w-12 h-12 border-4 border-pip-green rounded-full flex items-center justify-center bg-bg-screen">
                            <span class="text-terminal text-2xl text-pip-green font-bold">M</span>
                        </div>
                        <span class="text-future text-2xl text-pip-green glow-text tracking-wider">MANGOON</span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex md:items-center md:space-x-6">
                    <a href="{{ route('manga.index') }}" class="nav-link text-future uppercase tracking-wide hover:text-radiation-yellow transition-all flex items-center gap-2">
                        <i class="gg-layout-grid"></i> Browse
                    </a>

                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="nav-link text-future uppercase tracking-wide hover:text-radiation-yellow transition-all flex items-center gap-2">
                            <i class="gg-terminal"></i> Admin
                        </a>
                    @endauth

                    <div class="flex items-center space-x-3 pl-6 border-l-2 border-border">
                        @auth
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-sm">
                                    <span class="terminal-text">LOGOUT</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm">
                                <span class="terminal-text">LOGIN</span>
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" type="button" class="btn p-2">
                        <i class="gg-menu"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t-2 border-border bg-bg-panel">
            <div class="px-4 pt-4 pb-6 space-y-3">
                <a href="{{ route('manga.index') }}" class="block px-4 py-3 panel text-center text-future uppercase tracking-wide hover:text-radiation-yellow transition-all">
                    Browse Manga
                </a>

                @auth
                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 panel text-center text-future uppercase tracking-wide hover:text-radiation-yellow transition-all">
                        Admin Panel
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full btn">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block">
                        <button class="w-full btn">Login</button>
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    @if(session('success') || session('error') || session('warning') || session('info'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            @if(session('success'))
                <div class="alert alert-success fade-in">
                    <div class="flex items-center">
                        <i class="gg-check-o mr-3"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger fade-in">
                    <div class="flex items-center">
                        <i class="gg-close-o mr-3"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning fade-in">
                    <div class="flex items-center">
                        <i class="gg-danger mr-3"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-success fade-in">
                    <div class="flex items-center">
                        <i class="gg-info mr-3"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Content -->
    <main class="min-h-screen py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t-4 border-border bg-bg-panel mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Brand Section -->
                <div>
                    <h3 class="text-xl mb-4">MANGOON</h3>
                    <p class="text-text-primary opacity-80 text-sm leading-relaxed">
                        A retro-futuristic manga management system inspired by the Wasteland.
                        Read, organize, and manage your manga collection in style.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg mb-4 text-future uppercase text-radiation-yellow">Quick Access</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('manga.index') }}" class="hover:text-pip-green transition-colors flex items-center gap-2"><i class="gg-layout-grid"></i> Browse Manga</a></li>
                        @auth
                            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-pip-green transition-colors flex items-center gap-2"><i class="gg-terminal"></i> Admin Panel</a></li>
                            <li><a href="{{ route('admin.manga.index') }}" class="hover:text-pip-green transition-colors flex items-center gap-2"><i class="gg-list"></i> Manage Manga</a></li>
                        @endauth
                    </ul>
                </div>

                <!-- System Info -->
                <div>
                    <h4 class="text-lg mb-4 text-future uppercase text-radiation-yellow">System Status</h4>
                    <div class="terminal-text text-sm space-y-1">
                        <div class="flex justify-between">
                            <span>SYSTEM:</span>
                            <span class="text-pip-green">ONLINE</span>
                        </div>
                        <div class="flex justify-between">
                            <span>VERSION:</span>
                            <span class="text-pip-green">2.0.77</span>
                        </div>
                        <div class="flex justify-between">
                            <span>VAULT:</span>
                            <span class="text-pip-green">77</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="mt-12 pt-8 border-t-2 border-border text-center">
                <p class="text-sm text-text-primary opacity-70 terminal-text">
                    &copy; {{ date('Y') }} MANGOON SYSTEM - ALL RIGHTS RESERVED
                    <span class="text-pip-green ml-2">█</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
