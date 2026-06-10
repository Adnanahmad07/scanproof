@extends('layouts.auth')

@section('title', 'Reset Password - ScanProof')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-text-primary">Reset your password</h1>
        <p class="text-text-secondary mt-1">Enter your new password below</p>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="p-4 bg-error/10 border border-error/30 rounded-xl">
            <ul class="text-sm text-error space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Reset Password Form --}}
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-text-primary mb-1.5">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ $request->email ?? old('email') }}"
                required
                autofocus
                class="w-full px-4 py-3 border border-surface-high rounded-xl bg-surface-low focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-text-muted @error('email') border-error focus:border-error focus:ring-error/10 @enderror"
                placeholder="you@example.com"
            >
            @error('email')
                <p class="text-sm text-error mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- New Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-text-primary mb-1.5">New Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="new-password"
                class="w-full px-4 py-3 border border-surface-high rounded-xl bg-surface-low focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-text-muted @error('password') border-error focus:border-error focus:ring-error/10 @enderror"
                placeholder="Enter new password"
            >
            @error('password')
                <p class="text-sm text-error mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-text-primary mb-1.5">Confirm Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="w-full px-4 py-3 border border-surface-high rounded-xl bg-surface-low focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-text-muted @error('password_confirmation') border-error focus:border-error focus:ring-error/10 @enderror"
                placeholder="Repeat your password"
            >
            @error('password_confirmation')
                <p class="text-sm text-error mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2"
        >
            Reset Password
        </button>
    </form>

    {{-- Back to Login --}}
    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 text-sm font-medium text-text-secondary hover:text-text-primary transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Sign In
    </a>
</div>
@endsection
