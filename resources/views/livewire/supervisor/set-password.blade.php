<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/5 via-surface-low to-primary/10 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        {{-- Logo --}}
        <div class="text-center">
            <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary/25">
                <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-text-primary">Set Your Password</h2>
            <p class="text-sm text-text-secondary mt-2">Welcome to ScanProof! Create your password to get started.</p>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-surface-high">
            @if (session('error'))
                <div class="bg-error/10 border border-error/20 text-error px-4 py-3 rounded-xl mb-6 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit="setPassword" class="space-y-5">
                <div>
                    <label for="password" class="block text-sm font-medium text-text-secondary mb-1">Password</label>
                    <input type="password"
                           wire:model="password"
                           id="password"
                           class="w-full px-4 py-2.5 text-sm border border-surface-high rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10"
                           placeholder="Enter your password">
                    @error('password')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="passwordConfirmation" class="block text-sm font-medium text-text-secondary mb-1">Confirm Password</label>
                    <input type="password"
                           wire:model="password_confirmation"
                           id="passwordConfirmation"
                           class="w-full px-4 py-2.5 text-sm border border-surface-high rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10"
                           placeholder="Confirm your password">
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    Set Password & Continue
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-text-muted">
            You were invited as a Supervisor. After setting your password, you'll be redirected to your dashboard.
        </p>
    </div>
</div>
