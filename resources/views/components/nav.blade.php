<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <span class="text-xl font-bold text-text-primary">ScanProof</span>
            </div>

            <div class="hidden md:flex items-center gap-8">
                <a href="#features" class="text-text-secondary hover:text-primary transition-colors">Features</a>
                <a href="#how-it-works" class="text-text-secondary hover:text-primary transition-colors">How It Works</a>
                <a href="#pricing" class="text-text-secondary hover:text-primary transition-colors">Pricing</a>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="text-text-secondary hover:text-primary transition-colors font-medium">Login</a>
                <a href="{{ route('register') }}" class="bg-primary hover:bg-primary-dark text-white px-6 py-2.5 rounded-xl font-medium transition-colors">Get Started</a>
            </div>
        </div>
    </div>
</nav>
