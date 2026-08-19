@extends('layouts.auth')

@section('title', 'Register - ScanProof')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-text-primary">Create your account</h1>
        <p class="text-text-secondary mt-1">Get started with ScanProof today</p>
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

    {{-- Register Form --}}
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- Organization Name --}}
        <div>
            <label for="organization_name" class="block text-sm font-medium text-text-primary mb-1.5">Organization Name</label>
            <input
                type="text"
                id="organization_name"
                name="organization_name"
                value="{{ old('organization_name') }}"
                required
                autofocus
                class="w-full px-4 py-3 border border-surface-high rounded-xl bg-surface-low focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-text-muted @error('organization_name') border-error focus:border-error focus:ring-error/10 @enderror"
                placeholder="e.g. City Hospital, Green Valley Apartments"
            >
            @error('organization_name')
                <p class="text-sm text-error mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-text-primary mb-1.5">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
                class="w-full px-4 py-3 border border-surface-high rounded-xl bg-surface-low focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-text-muted @error('name') border-error focus:border-error focus:ring-error/10 @enderror"
                placeholder="John Doe"
            >
            @error('name')
                <p class="text-sm text-error mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-text-primary mb-1.5">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                class="w-full px-4 py-3 border border-surface-high rounded-xl bg-surface-low focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-text-muted @error('email') border-error focus:border-error focus:ring-error/10 @enderror"
                placeholder="you@example.com"
            >
            @error('email')
                <p class="text-sm text-error mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-text-primary mb-1.5">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="w-full px-4 py-3 border border-surface-high rounded-xl bg-surface-low focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-text-muted @error('password') border-error focus:border-error focus:ring-error/10 @enderror"
                placeholder="Create a strong password"
            >
            @error('password')
                <p class="text-sm text-error mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password Confirmation --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-text-primary mb-1.5">Confirm Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                class="w-full px-4 py-3 border border-surface-high rounded-xl bg-surface-low focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-text-muted"
                placeholder="Repeat your password"
            >
        </div>

        {{-- Terms --}}
        <p class="text-xs text-text-secondary">
            By creating an account, you agree to our
            <a href="#" class="text-primary hover:text-primary-dark font-medium">Terms of Service</a>
            and
            <a href="#" class="text-primary hover:text-primary-dark font-medium">Privacy Policy</a>.
        </p>

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2"
        >
            Create Account
        </button>
    </form>

    {{-- Login Link --}}
    <p class="text-center text-sm text-text-secondary">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-primary hover:text-primary-dark transition-colors">Sign in</a>
    </p>
</div>
@endsection
