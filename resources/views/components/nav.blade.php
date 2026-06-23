<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <a href="/" class="flex items-center gap-2">
                <x-application-logo class="w-32 h-10" />
                <span class="text-xl font-bold text-text-primary">ScanProof</span>
            </a>

            <div class="hidden md:flex items-center gap-8">
                <a href="#features" class="text-text-secondary hover:text-primary transition-colors">Features</a>
                <a href="#how-it-works" class="text-text-secondary hover:text-primary transition-colors">How It Works</a>
                <a href="{{ route('pricing') }}" class="text-text-secondary hover:text-primary transition-colors">Pricing</a>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="text-text-secondary hover:text-primary transition-colors font-medium">Login</a>
                <a href="{{ route('register') }}" class="bg-primary hover:bg-primary-dark text-white px-6 py-2.5 rounded-xl font-medium transition-colors">Get Started</a>
            </div>
        </div>
    </div>
</nav>
