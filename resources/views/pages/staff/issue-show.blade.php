@extends('layouts.staff')

@section('title', 'Issue Details - ScanProof')
@section('page-title', 'Issue Details')
@section('page-subtitle', $issue->tracking_code)

@section('content')
<div class="max-w-lg mx-auto space-y-6">
    @if (session('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-surface-high p-6">
        {{-- Header --}}
        <div class="text-center mb-6">
            <p class="text-xs text-text-muted uppercase tracking-wide">Tracking Code</p>
            <h1 class="text-2xl font-bold text-text-primary font-mono">{{ $issue->tracking_code }}</h1>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium mt-2
                {{ match($issue->status) {
                    'assigned' => 'bg-info/10 text-info',
                    'in_progress' => 'bg-primary/10 text-primary',
                    'resolved' => 'bg-success/10 text-success',
                    default => 'bg-surface-low text-text-muted',
                } }}">
                {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
            </span>
        </div>

        {{-- Location --}}
        <div class="flex items-center gap-3 p-3 bg-surface-low rounded-xl mb-4">
            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $issue->location->type->icon() }}" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-text-primary">{{ $issue->location->name }}</p>
                <p class="text-xs text-text-muted">{{ $issue->location->type->label() }}</p>
            </div>
        </div>

        {{-- Description --}}
        <div class="p-3 bg-surface-low rounded-xl mb-4">
            <p class="text-xs text-text-muted uppercase tracking-wide mb-1">Description</p>
            <p class="text-sm text-text-primary">{{ $issue->description }}</p>
        </div>

        {{-- Photo --}}
        @if ($issue->photo_path)
            <div class="mb-4">
                <img src="{{ Storage::url($issue->photo_path) }}" alt="Issue photo" class="w-full rounded-xl">
            </div>
        @endif

        {{-- Actions --}}
        @if ($issue->status !== 'resolved')
            <div class="flex gap-3">
                @if ($issue->status === 'assigned')
                    <form method="POST" action="{{ route('staff.issues.status', $issue->id) }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                            Start Working
                        </button>
                    </form>
                @elseif ($issue->status === 'in_progress')
                    <form method="POST" action="{{ route('staff.issues.status', $issue->id) }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="status" value="resolved">
                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-success text-white rounded-xl text-sm font-medium hover:bg-success/90 transition-colors">
                            Mark Complete
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
