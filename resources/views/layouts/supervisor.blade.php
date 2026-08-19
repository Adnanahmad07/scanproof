<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard - ScanProof')</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpeg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles
</head>
<body class="bg-surface-low font-sans text-text-primary antialiased">

    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col lg:flex-row">

        {{-- Mobile Header --}}
        <header class="lg:hidden h-16 bg-white border-b border-surface-high flex items-center px-4 sticky top-0 z-30">
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 -ml-2 text-text-secondary hover:text-text-primary hover:bg-surface-container rounded-xl transition-colors" aria-label="Toggle sidebar">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="flex-1 mx-3">
                <h1 class="text-lg font-bold text-text-primary">@yield('page-title', 'Dashboard')</h1>
            </div>
        </header>

        {{-- Sidebar Overlay (mobile) --}}
        <template x-teleport="body">
            <div x-show="sidebarOpen" @click="sidebarOpen = false"
                 x-transition:enter="transition-opacity ease-linear duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden">
            </div>
        </template>

        {{-- Sidebar --}}
        <aside class="
            fixed lg:sticky inset-y-0 left-0 z-50
            w-64 lg:w-72
            bg-white lg:bg-surface-container-low
            border-r border-surface-high
            flex flex-col
            h-screen
            transform transition-transform duration-200 ease-in-out
            lg:transform-none
            -translate-x-full lg:translate-x-0
        " :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            {{-- Logo --}}
            <div class="h-20 flex items-center gap-3 px-6 border-b border-surface-high">
                <x-application-logo class="w-32 h-10" />
                <span class="text-xl font-bold text-text-primary tracking-tight">ScanProof</span>
            </div>

            {{-- Mobile close button --}}
            <div class="lg:hidden flex items-center justify-between h-16 px-4 border-b border-surface-high">
                <span class="text-lg font-bold text-text-primary">Menu</span>
                <button @click="sidebarOpen = false" class="p-2 text-text-secondary hover:text-text-primary hover:bg-surface-container rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- User Profile --}}
            <div class="px-4 py-4">
                <div class="flex items-center gap-3 px-3 py-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-info to-info/80 rounded-xl flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                        {{ substr(auth()->user()->name ?? 'S', 0, 2) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-text-primary truncate">{{ auth()->user()->name ?? 'Supervisor' }}</p>
                        <p class="text-xs text-text-muted truncate">{{ auth()->user()->organization->name ?? 'Supervisor' }}</p>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 py-2 px-3 space-y-1 overflow-y-auto">
                <a href="{{ route('supervisor.dashboard') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('supervisor.dashboard') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('supervisor.scan-qr') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('supervisor.scan-qr*') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <span>Scan QR</span>
                </a>

                <a href="{{ route('supervisor.issues.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('supervisor.issues*') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Issues</span>
                </a>

                <a href="{{ route('supervisor.tasks') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('supervisor.tasks') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Tasks</span>
                </a>

                <a href="{{ route('supervisor.recurring-tasks') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('supervisor.recurring-tasks') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Recurring</span>
                </a>

                <a href="{{ route('supervisor.workers') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('supervisor.workers') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Workers</span>
                </a>
            </nav>

            {{-- Sidebar Footer --}}
            <div class="p-3 border-t border-surface-high space-y-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 w-full px-4 py-2.5 rounded-xl text-sm font-medium text-text-secondary hover:text-error hover:bg-error/5 transition-all">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Desktop Top Bar --}}
            <header class="hidden lg:flex h-20 bg-white border-b border-surface-high items-center justify-between sticky top-0 z-10 px-8">
                <div class="flex-1 max-w-xl">
                    <livewire:global-search />
                </div>
                <div class="flex items-center gap-3 ml-4">
                    <livewire:notification-dropdown />
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-4 md:p-6 lg:p-8 xl:px-12 xl:py-8">
                {{ $slot ?? '' }}@yield('content')
            </main>
        </div>
    </div>

    @livewireScripts

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        window.scanQrActive = null;

        window.startScanQr = function() {
            var el = document.getElementById('qr-reader');
            if (!el) return;
            if (window.scanQrActive) { try { window.scanQrActive.clear(); } catch(e) {} window.scanQrActive = null; }

            var scanner = new Html5Qrcode("qr-reader");
            window.scanQrActive = scanner;
            var scanned = false;

            scanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                function(decodedText) {
                    if (scanned) return;
                    scanned = true;
                    scanner.stop().then(function() {
                        window.scanQrActive = null;
                        window.dispatchEvent(new CustomEvent('qr-scanned', { detail: { uuid: decodedText } }));
                    }).catch(function() {});
                },
                function(errorMessage) {}
            ).catch(function(err) {
                console.log("Camera error:", err);
                el.innerHTML = '<div class="w-full h-64 bg-surface-low flex items-center justify-center"><div class="text-center p-4"><p class="text-sm text-text-primary font-medium">Camera access required</p><p class="text-xs text-text-muted mt-1">Please allow camera access</p></div></div>';
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('qr-reader')) {
                setTimeout(window.startScanQr, 300);
            }
        });

        document.addEventListener('livewire:message.processed', function() {
            if (document.getElementById('qr-reader')) {
                setTimeout(window.startScanQr, 300);
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
