<div class="space-y-6">
    {{-- Page Header --}}
    <div>
        <h1 class="text-xl font-bold text-text-primary">My Issues</h1>
        <p class="text-xs text-text-secondary mt-0.5">Issues assigned to you</p>
    </div>

    {{-- Issues List --}}
    <div class="bg-white rounded-2xl border border-surface-high">
        @if ($issues->isEmpty())
            <div class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-text-secondary font-medium">No issues assigned</p>
                <p class="text-text-muted text-sm mt-1">Issues assigned to you will appear here.</p>
            </div>
        @else
            @foreach ($issues as $issue)
                <div class="px-4 md:px-6 py-4 border-b border-surface-high/50 last:border-b-0 hover:bg-surface-low/30 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono text-text-muted">{{ $issue->tracking_code }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ match($issue->status) {
                                        'assigned' => 'bg-info/10 text-info',
                                        'in_progress' => 'bg-primary/10 text-primary',
                                        default => 'bg-surface-low text-text-muted',
                                    } }}">
                                    {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                                </span>
                            </div>
                            <p class="text-sm text-text-primary mt-1 line-clamp-2">{{ $issue->description }}</p>
                            <p class="text-xs text-text-muted mt-2">{{ $issue->location->name }} &middot; {{ $issue->created_at->diffForHumans() }}</p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2">
                            @if ($issue->status === 'assigned')
                                <form method="POST" action="{{ route('staff.issues.status', $issue->id) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="in_progress">
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors">
                                        Start
                                    </button>
                                </form>
                            @elseif ($issue->status === 'in_progress')
                                <form method="POST" action="{{ route('staff.issues.status', $issue->id) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="resolved">
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium text-white bg-success rounded-lg hover:bg-success/90 transition-colors">
                                        Complete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
