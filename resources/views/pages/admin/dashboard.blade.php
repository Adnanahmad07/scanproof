@extends('layouts.admin')

@section('title', 'Admin Dashboard - ScanProof')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your facility')

@php
    $analytics = app(\App\Services\AnalyticsService::class);
    $stats = $analytics->getDashboardStats();
    $healthScore = app(\App\Services\HealthScoreService::class)->calculate();
@endphp

@section('content')
    <div class="space-y-6">

        {{-- Welcome Banner --}}
        <div class="bg-gradient-to-r from-primary to-primary-dark rounded-2xl p-6 text-white">
            <h2 class="text-xl font-bold">Welcome back, {{ auth()->user()->name }}!</h2>
            <p class="mt-1 text-white/80 text-sm">Here's what's happening with your facilities today.</p>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-surface-high">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-text-muted">Total Staff</p>
                        <p class="text-2xl font-bold text-text-primary mt-1">{{ $stats['totalStaff'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-surface-high">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-text-muted">Active Tasks</p>
                        <p class="text-2xl font-bold text-text-primary mt-1">{{ $stats['activeTasks'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-surface-high">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-text-muted">Locations</p>
                        <p class="text-2xl font-bold text-text-primary mt-1">{{ $stats['totalLocations'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-info/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.issues.index') }}" class="bg-white rounded-2xl p-5 border border-surface-high hover:border-warning/50 transition-colors block">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-text-muted">Open Issues</p>
                        <p class="text-2xl font-bold text-text-primary mt-1">{{ $stats['openIssues'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                </div>
            </a>
        </div>

        {{-- Facility Health --}}
        <div class="bg-white rounded-2xl p-6 border border-surface-high">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-text-primary">Facility Health</h3>
                    <p class="text-sm text-text-muted mt-1">Overall performance score</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-3xl font-bold text-{{ $healthScore >= 60 ? 'success' : ($healthScore >= 40 ? 'warning' : 'error') }}">
                            {{ $healthScore }}
                        </p>
                        <p class="text-xs text-text-muted">/ 100</p>
                    </div>
                    <a href="{{ route('admin.reports.index') }}"
                       class="px-4 py-2 bg-primary/10 text-primary rounded-xl text-sm font-medium hover:bg-primary/20 transition-colors">
                        View Details
                    </a>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-2xl p-6 border border-surface-high">
            <h3 class="text-lg font-semibold text-text-primary mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="{{ route('admin.supervisors.index') }}"
                   class="flex items-center gap-3 p-4 rounded-xl border border-surface-high hover:border-primary hover:bg-primary/5 transition-all">
                    <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-text-primary">Invite Supervisor</p>
                        <p class="text-xs text-text-muted">Send invitation email</p>
                    </div>
                </a>

                <a href="{{ route('admin.reports.index') }}"
                   class="flex items-center gap-3 p-4 rounded-xl border border-surface-high hover:border-primary hover:bg-primary/5 transition-all">
                    <div class="w-10 h-10 bg-success/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-text-primary">View Reports</p>
                        <p class="text-xs text-text-muted">Analytics &amp; exports</p>
                    </div>
                </a>

                <a href="{{ route('admin.locations.index') }}"
                   class="flex items-center gap-3 p-4 rounded-xl border border-surface-high hover:border-primary hover:bg-primary/5 transition-all">
                    <div class="w-10 h-10 bg-warning/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-text-primary">Locations</p>
                        <p class="text-xs text-text-muted">Manage facilities</p>
                    </div>
                </a>
            </div>
        </div>

    </div>
@endsection
