<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report Issue - {{ $location->name }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpeg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body class="bg-surface-low min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        @if (session('success'))
            <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded-xl text-sm mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-surface-high p-6">
            {{-- Room Info --}}
            <div class="text-center mb-6">
                <div class="w-12 h-12 mx-auto mb-3 bg-primary/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $location->type->icon() }}" />
                    </svg>
                </div>
                <h1 class="text-lg font-bold text-text-primary">{{ $location->name }}</h1>
                <p class="text-sm text-text-muted">{{ $location->type->label() }}</p>
            </div>

            {{-- Issue Form --}}
            <form method="POST" action="{{ route('scan.report.submit', $location->uuid) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                {{-- Honeypot field: hidden from humans, bots will fill it --}}
                <div style="position: absolute; left: -9999px;" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-primary mb-1">What's the problem? *</label>
                    <textarea name="description" rows="4" required
                              class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                              placeholder="Describe the issue...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-primary mb-1">Add Photo (optional)</label>
                    <input type="file" name="photo" accept="image/*"
                           class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    <p class="text-xs text-text-muted mt-1">Max 5MB. JPG, PNG, or WebP.</p>
                    @error('photo')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full px-6 py-3 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                    Submit Report
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-text-muted mt-4">ScanProof &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
