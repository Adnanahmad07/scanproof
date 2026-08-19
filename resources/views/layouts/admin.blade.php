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

        {{-- ========================================================================= --}}
        {{-- MOBILE HEADER (visible < lg) --}}
        {{-- ========================================================================= --}}
        <header class="lg:hidden h-16 bg-white border-b border-surface-high flex items-center px-4 sticky top-0 z-30">
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 -ml-2 text-text-secondary hover:text-text-primary hover:bg-surface-container rounded-xl transition-colors" aria-label="Toggle sidebar">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="flex-1 mx-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" placeholder="Search..."
                           class="w-full pl-10 pr-4 py-2 text-sm bg-surface-low border border-surface-high rounded-xl placeholder:text-text-muted focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">
                </div>
            </div>

            <button class="relative p-2 text-text-secondary hover:text-text-primary hover:bg-surface-container rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
            </button>
        </header>

        {{-- ========================================================================= --}}
        {{-- SIDEBAR OVERLAY (mobile) --}}
        {{-- ========================================================================= --}}
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

        {{-- ========================================================================= --}}
        {{-- SIDEBAR --}}
        {{-- ========================================================================= --}}
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
                <span class="text-xl font-bold text-text-primary tracking-tight">Scanproof</span>
            </div>

            {{-- Mobile close button --}}
            <div class="lg:hidden flex items-center justify-between h-16 px-4 border-b border-surface-high">
                <div class="flex items-center gap-2">
                    <x-application-logo class="w-24 h-8" />
                    <span class="text-lg font-bold text-text-primary">Scanproof</span>
                </div>
                <button @click="sidebarOpen = false" class="p-2 text-text-secondary hover:text-text-primary hover:bg-surface-container rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- User Profile --}}
            <div class="px-4 py-4">
                <div class="flex items-center gap-3 px-3 py-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary to-primary-dark rounded-xl flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                        {{ substr(auth()->user()->name ?? 'A', 0, 2) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-text-primary truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-text-muted truncate">{{ auth()->user()->organization->name ?? 'Admin' }}</p>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 py-2 px-3 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('dashboard') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.users.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('admin.users.*') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Users</span>
                </a>

                <a href="{{ route('admin.supervisors.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('admin.supervisors.*') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Supervisors</span>
                </a>

                <a href="{{ route('admin.locations.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('admin.locations.*') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Locations</span>
                </a>

                <a href="{{ route('admin.issues.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('admin.issues.*') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Issues</span>
                </a>

                <a href="{{ route('admin.reports.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all border-r-2 {{ request()->routeIs('admin.reports.*') ? 'text-primary bg-primary/5 border-r-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-container border-r-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Reports</span>
                </a>


            </nav>

            {{-- Sidebar Footer --}}
            <div class="p-3 border-t border-surface-high space-y-1">
                <a href="#" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-text-secondary hover:text-text-primary hover:bg-surface-container transition-all">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Support</span>
                </a>

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

        {{-- ========================================================================= --}}
        {{-- MAIN CONTENT AREA --}}
        {{-- ========================================================================= --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- DESKTOP TOP BAR (visible >= lg) --}}
            <header class="hidden lg:flex h-20 bg-white border-b border-surface-high items-center justify-between sticky top-0 z-10">
                <div class="flex-1"></div>

                {{-- Global Search (centered) --}}
                <div class="relative flex-1 max-w-xl px-4">
                    <livewire:global-search />
                </div>

                <div class="flex-1 flex items-center justify-end gap-4">
                    <livewire:notification-dropdown />
                </div>
            </header>

            {{-- Mobile page title bar --}}
            <div class="lg:hidden bg-white border-b border-surface-high px-4 py-3">
                <h1 class="text-lg font-bold text-text-primary">@yield('page-title', 'Dashboard')</h1>
                <p class="text-xs text-text-secondary mt-0.5">@yield('page-subtitle', 'Overview of your facility')</p>
            </div>

            {{-- Page Content --}}
            <main class="flex-1 p-4 md:p-6 lg:p-8 xl:px-12 xl:py-8 2xl:px-16 2xl:py-10 pb-24 lg:pb-8">
                {{ $slot ?? '' }}@yield('content')
            </main>
        </div>

        {{-- ========================================================================= --}}
        {{-- BOTTOM NAV (mobile) --}}
        {{-- ========================================================================= --}}
        <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-surface-high safe-area-bottom">
            <div class="flex items-center justify-around h-16 px-2">
                <a href="{{ route('dashboard') }}"
                   class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-text-muted hover:text-text-secondary' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-[10px] font-medium">Dashboard</span>
                </a>

                {{-- Scan FAB --}}
                <a href="#"
                   class="flex flex-col items-center -mt-5">
                    <div class="w-14 h-14 bg-primary rounded-2xl flex items-center justify-center shadow-lg shadow-primary/25 text-white hover:bg-primary-dark transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-medium text-text-muted mt-0.5">Scan</span>
                </a>

                <a href="{{ route('admin.locations.index') }}"
                   class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors {{ request()->routeIs('admin.locations.*') ? 'text-primary' : 'text-text-muted hover:text-text-secondary' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-[10px] font-medium">Locations</span>
                </a>

            </div>
        </nav>
    </div>

    @livewireScripts

    @stack('scripts')
</body>
</html>
