@extends('layouts.client')

@section('title', 'Client Dashboard - ScanProof')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, {{ auth()->user()->name }}')

@section('content')
    <div class="space-y-6">

        {{-- Welcome Banner --}}
        <div class="bg-gradient-to-r from-warning to-warning/80 rounded-2xl p-6 text-white">
            <h2 class="text-xl font-bold">Welcome back, {{ auth()->user()->name }}!</h2>
            <p class="mt-1 text-white/80 text-sm">Here's an overview of your facility status.</p>
        </div>

        {{-- Info Card --}}
        <div class="bg-white rounded-2xl p-6 border border-surface-high">
            <h3 class="text-lg font-semibold text-text-primary mb-4">Your Facility</h3>
            <p class="text-sm text-text-secondary">
                This portal provides you with visibility into your facility's operations.
                Your supervisor manages task assignments and issue resolutions.
            </p>
        </div>

    </div>
@endsection
