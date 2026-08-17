<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan to Solve - ScanProof</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpeg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body class="bg-surface-low min-h-screen">
    {{-- Header --}}
    <div class="bg-white border-b border-surface-high px-4 py-3">
        <div class="max-w-sm mx-auto flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span class="font-bold text-text-primary">ScanProof</span>
            </a>
            <span class="text-xs text-text-muted">Scan to Solve</span>
        </div>
    </div>

    <div class="max-w-sm mx-auto px-4 py-4">
        @if (session('success'))
            <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded-xl text-sm mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Camera Scanner (Full Width - Primary Action) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-surface-high overflow-hidden mb-4">
            <div id="qr-reader" class="w-full" style="min-height: 350px;"></div>
            <div class="p-4 text-center border-t border-surface-high">
                <p class="text-sm text-text-primary font-medium">Point your camera at a QR code</p>
                <p class="text-xs text-text-muted mt-1">The code is usually on the wall or door</p>
            </div>
        </div>

        {{-- Manual Code Entry (Small - Secondary) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-surface-high p-4 mb-4">
            <form id="manual-entry-form" class="flex gap-2">
                <input type="text" id="manual-code-input"
                       class="flex-1 px-3 py-2 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                       placeholder="Or enter code manually">
                <button type="submit"
                        class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                    Go
                </button>
            </form>
        </div>

        {{-- Track Existing Issue (Small Text Link) --}}
        <div class="text-center mb-4">
            <button onclick="document.getElementById('track-section').classList.toggle('hidden')"
                    class="text-xs text-text-muted hover:text-primary transition-colors">
                Track Existing Issue
            </button>
        </div>

        {{-- Track Section (Hidden by default) --}}
        <div id="track-section" class="hidden bg-white rounded-2xl shadow-sm border border-surface-high p-4 mb-4">
            <form method="POST" action="{{ route('scan.track.lookup') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-text-muted mb-1">Enter your tracking code</label>
                    <input type="text" name="tracking_code" value="{{ old('tracking_code') }}" required
                           class="w-full px-3 py-2 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                           placeholder="e.g., SP-ABC123">
                    @error('tracking_code')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        class="w-full px-4 py-2 bg-surface-low hover:bg-surface-high text-text-primary rounded-xl text-sm font-medium transition-colors border border-surface-high">
                    Track Issue
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-text-muted mt-4">ScanProof &copy; {{ date('Y') }}</p>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const html5QrCode = new Html5Qrcode("qr-reader");
            let scanned = false;

            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    if (scanned) return;
                    scanned = true;
                    html5QrCode.stop().catch(() => {});

                    let uuid = decodedText;
                    const match = decodedText.match(/\/r\/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i);
                    if (match) uuid = match[1];

                    document.getElementById('qr-reader').innerHTML =
                        '<div class="w-full h-64 bg-surface-low flex items-center justify-center">' +
                        '<div class="text-center p-4"><p class="text-sm text-primary font-medium">QR detected! Redirecting...</p></div></div>';

                    window.location.href = '/r/' + uuid;
                },
                (errorMessage) => {}
            ).catch((err) => {
                document.getElementById('qr-reader').innerHTML =
                    '<div class="w-full h-64 bg-surface-low flex items-center justify-center">' +
                    '<div class="text-center p-4"><p class="text-sm text-text-primary font-medium">Camera access required</p>' +
                    '<p class="text-xs text-text-muted mt-1">Please allow camera access</p></div></div>';
            });

            // Manual code entry
            document.getElementById('manual-entry-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const code = document.getElementById('manual-code-input').value.trim();
                if (code) {
                    window.location.href = '/r/' + code;
                }
            });
        });
    </script>
</body>
</html>
