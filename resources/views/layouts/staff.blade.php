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
                    <div class="w-10 h-10 bg-gradient-to-br from-success to-success/80 rounded-xl flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                        {{ substr(auth()->user()->name ?? 'W', 0, 2) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-text-primary truncate">{{ auth()->user()->name ?? 'Worker' }}</p>
                        <p class="text-xs text-text-muted truncate">Staff</p>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 py-2 px-3 space-y-1 overflow-y-auto">
                <a href="{{ route('staff.dashboard') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('staff.dashboard') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('staff.tasks') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('staff.tasks') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>My Tasks</span>
                </a>

                <a href="{{ route('staff.scan') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('staff.scan') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <span>Scan QR</span>
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
                <div>
                    <h1 class="text-xl font-bold text-text-primary">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-text-secondary mt-0.5">@yield('page-subtitle', '')</p>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-4 md:p-6 lg:p-8 xl:px-12 xl:py-8">
                @yield('content')
            </main>
        </div>
    </div>

    @livewireStyles
    @livewireScripts

    @stack('scripts')
</body>
</html>
