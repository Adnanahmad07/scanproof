@extends('layouts.admin')

@section('title', 'Issues - ScanProof')
@section('page-title', 'All Issues')
@section('page-subtitle', 'View and monitor all reported issues across facilities')

@section('content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl border border-surface-high p-4">
            <p class="text-2xl font-bold text-text-primary">{{ $stats['total'] }}</p>
            <p class="text-xs text-text-muted">Total Issues</p>
        </div>
        <div class="bg-white rounded-xl border border-surface-high p-4">
            <p class="text-2xl font-bold text-warning">{{ $stats['reported'] }}</p>
            <p class="text-xs text-text-muted">New Reports</p>
        </div>
        <div class="bg-white rounded-xl border border-surface-high p-4">
            <p class="text-2xl font-bold text-info">{{ $stats['assigned'] }}</p>
            <p class="text-xs text-text-muted">Assigned</p>
        </div>
        <div class="bg-white rounded-xl border border-surface-high p-4">
            <p class="text-2xl font-bold text-primary">{{ $stats['in_progress'] }}</p>
            <p class="text-xs text-text-muted">In Progress</p>
        </div>
        <div class="bg-white rounded-xl border border-surface-high p-4">
            <p class="text-2xl font-bold text-success">{{ $stats['resolved'] }}</p>
            <p class="text-xs text-text-muted">Resolved</p>
        </div>
    </div>

    {{-- Issues List --}}
    <div class="bg-white rounded-2xl border border-surface-high">
        @if ($issues->isEmpty())
            <div class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-text-secondary font-medium">No issues yet</p>
                <p class="text-text-muted text-sm mt-1">Issues reported from scanned QR codes will appear here.</p>
            </div>
        @else
            <div class="divide-y divide-surface-high/50">
                @foreach ($issues as $issue)
                    <div class="px-4 md:px-6 py-4 hover:bg-surface-low/30 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                {{-- Status & Tracking --}}
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ match($issue->status) {
                                            'reported' => 'bg-warning/10 text-warning',
                                            'assigned' => 'bg-info/10 text-info',
                                            'in_progress' => 'bg-primary/10 text-primary',
                                            'resolved' => 'bg-success/10 text-success',
                                            default => 'bg-surface-low text-text-muted',
                                        } }}">
                                        {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                                    </span>
                                    <span class="text-xs font-mono text-text-muted">{{ $issue->tracking_code }}</span>
                                </div>

                                {{-- Description --}}
                                <p class="text-sm text-text-primary line-clamp-2">{{ $issue->description }}</p>

                                {{-- Photo indicator --}}
                                @if ($issue->photo_path)
                                    <span class="inline-flex items-center gap-1 mt-1 text-xs text-text-muted">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Photo attached
                                    </span>
                                @endif

                                {{-- Meta --}}
                                <div class="flex items-center gap-3 mt-2 text-xs text-text-muted flex-wrap">
                                    @if ($issue->location)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            {{ $issue->location->name }}
                                        </span>
                                    @endif
                                    <span>{{ $issue->created_at->diffForHumans() }}</span>
                                    @if ($issue->assignee)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ $issue->assignee->name }}
                                        </span>
                                    @endif
                                    @if ($issue->reporter)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                            </svg>
                                            {{ $issue->reporter->name ?? 'Anonymous' }}
                                        </span>
                                    @endif
                                    @if ($issue->task)
                                        <span class="flex items-center gap-1 text-success">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                            </svg>
                                            Task #{{ $issue->task->id }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
