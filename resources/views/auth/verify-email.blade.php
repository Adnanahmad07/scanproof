@extends('layouts.auth')

@section('title', 'Verify Email - ScanProof')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="text-center">
        <div class="mx-auto w-16 h-16 bg-info/10 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-text-primary">Verify your email</h1>
        <p class="text-text-secondary mt-1">We've sent a verification link to your email</p>
    </div>

    {{-- Success Message --}}
    @if (session('resent'))
        <div class="p-4 bg-success/10 border border-success/30 rounded-xl">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-success font-medium">A fresh verification link has been sent to your email address.</p>
            </div>
        </div>
    @endif

    {{-- Email Display --}}
    <div class="p-4 bg-surface-container rounded-xl border border-surface-high text-center">
        <p class="text-sm text-text-secondary mb-1">Verification email sent to</p>
        <p class="text-base font-semibold text-text-primary break-all">{{ Auth::user()->email }}</p>
    </div>

    {{-- Instructions --}}
    <div class="p-4 bg-info/5 border border-info/20 rounded-xl">
        <p class="text-sm text-text-secondary text-center">
            Check your inbox and click the verification link. If you didn't receive the email, click below to resend.
        </p>
    </div>

    {{-- Resend Form --}}
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button
            type="submit"
            class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2"
        >
            Resend Verification Email
        </button>
    </form>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button
            type="submit"
            class="w-full border border-surface-high text-text-secondary hover:bg-surface-container hover:text-text-primary font-semibold py-3 rounded-xl transition-colors"
        >
            Sign Out
        </button>
    </form>
</div>
@endsection
