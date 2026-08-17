<section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-primary/5 to-white">
    <div class="max-w-7xl mx-auto">
        <div class="text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-sm font-medium mb-6">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Facility Operations Platform
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-text-primary leading-tight mb-6">
                Streamline Your
                <span class="text-primary">Facility Operations</span>
            </h1>

            <p class="text-lg sm:text-xl text-text-secondary mb-10 max-w-2xl mx-auto">
                QR-based scanning for cleaning, maintenance, and facility management. Track tasks, generate reports, and ensure compliance with ease.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('scan.homeowner') }}" class="bg-primary hover:bg-primary-dark text-white px-8 py-4 rounded-xl font-semibold text-lg transition-colors inline-flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    Scan to Solve
                </a>
                <a href="{{ route('scan.worker') }}" class="bg-white hover:bg-surface-low text-text-primary px-8 py-4 rounded-xl font-semibold text-lg border border-gray-200 transition-colors inline-flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    I'm a Worker
                </a>
            </div>
        </div>

        <div class="mt-16 relative">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-2 max-w-5xl mx-auto">
                <div class="bg-surface-low rounded-xl aspect-video flex items-center justify-center">
                    <div class="text-center text-text-muted">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <p class="font-medium">Dashboard Preview</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
