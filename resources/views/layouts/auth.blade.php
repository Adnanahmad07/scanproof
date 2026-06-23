<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ScanProof - Authentication')</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpeg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles

    <style>
        .auth-illustration {
            background: linear-gradient(135deg, #0B5FFF 0%, #0A4ED6 50%, #3B7FFF 100%);
        }
        .auth-pattern {
            background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.15) 1px, transparent 0);
            background-size: 32px 32px;
        }
    </style>
</head>
<body class="bg-surface font-sans text-text-primary antialiased">
    <div class="min-h-screen flex">
        {{-- Left Panel: Illustration (hidden on mobile, visible on lg+) --}}
        <div class="hidden lg:flex lg:w-1/2 auth-illustration relative overflow-hidden">
            {{-- Pattern overlay --}}
            <div class="absolute inset-0 auth-pattern"></div>

            {{-- Decorative circles --}}
            <div class="absolute top-20 left-10 w-72 h-72 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-white/5 rounded-full blur-2xl"></div>

            {{-- Content --}}
            <div class="relative z-10 flex flex-col justify-between p-12 w-full">
                {{-- Logo --}}
                <div class="flex items-center gap-3">
                    <x-application-logo class="w-36 h-11 bg-white shadow-lg" />
                    <span class="text-2xl font-bold text-white tracking-tight">ScanProof</span>
                </div>

                {{-- Center Illustration --}}
                <div class="flex-1 flex items-center justify-center">
                    <div class="text-center text-white space-y-6">
                        {{-- Large Illustration --}}
                        <div class="mx-auto w-64 h-64 bg-white/10 rounded-3xl backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-2xl p-12">
                            <x-application-logo class="w-full h-auto opacity-90" />
                        </div>

                        <div class="space-y-3">
                            <h2 class="text-3xl font-bold">Streamline Your Facility Operations</h2>
                            <p class="text-white/80 text-lg max-w-md mx-auto">
                                QR-based task tracking, automated reports, and real-time facility management.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Bottom Stats --}}
                <div class="grid grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">500+</div>
                        <div class="text-sm text-white/70">Facilities</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">98%</div>
                        <div class="text-sm text-white/70">Compliance</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">24/7</div>
                        <div class="text-sm text-white/70">Tracking</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Panel: Form --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center px-4 sm:px-6 lg:px-12 py-12 bg-surface">
            <div class="w-full max-w-md">
                {{-- Mobile-only Logo --}}
                <div class="lg:hidden flex items-center gap-2 mb-8">
                    <x-application-logo class="w-32 h-10" />
                    <span class="text-xl font-bold text-text-primary">ScanProof</span>
                </div>

                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
