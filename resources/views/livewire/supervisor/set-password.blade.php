<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/5 via-surface-low to-primary/10 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        {{-- Logo --}}
        <div class="text-center">
            <x-application-logo class="w-48 h-16 mx-auto mb-4 shadow-lg shadow-primary/25" />
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
