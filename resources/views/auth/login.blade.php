@extends('layouts.auth')

@section('title', 'Login - ScanProof')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-text-primary">Welcome back</h1>
        <p class="text-text-secondary mt-1">Sign in to your account to continue</p>
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

    {{-- Login Form --}}
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-text-primary mb-1.5">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
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
                placeholder="Enter your password"
            >
            @error('password')
                <p class="text-sm text-error mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember Me & Forgot Password --}}
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    class="w-4 h-4 rounded border-surface-high text-primary focus:ring-primary/20 cursor-pointer"
                >
                <span class="text-sm text-text-secondary">Remember me</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary hover:text-primary-dark transition-colors">
                Forgot password?
            </a>
        </div>

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2"
        >
            Sign In
        </button>
    </form>

    {{-- Register Link --}}
    <p class="text-center text-sm text-text-secondary">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-medium text-primary hover:text-primary-dark transition-colors">Create an account</a>
    </p>
</div>
@endsection
