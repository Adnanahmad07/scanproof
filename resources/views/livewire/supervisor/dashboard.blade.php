<div class="space-y-6">

    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-r from-info to-info/80 rounded-2xl p-6 text-white">
        <h2 class="text-xl font-bold">Welcome, {{ auth()->user()->name }}!</h2>
        <p class="mt-1 text-white/80 text-sm">Here's an overview of your facilities and team.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-surface-high">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-text-muted">Total Tasks</p>
                    <p class="text-2xl font-bold text-text-primary mt-1">{{ $stats['totalTasks'] }}</p>
                </div>
                <div class="w-12 h-12 bg-info/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-surface-high">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-text-muted">Completed</p>
                    <p class="text-2xl font-bold text-success mt-1">{{ $stats['completedTasks'] }}</p>
                </div>
                <div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-surface-high">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-text-muted">Active Locations</p>
                    <p class="text-2xl font-bold text-primary mt-1">{{ $stats['activeLocations'] }}</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-surface-high">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-text-muted">Open Issues</p>
                    <p class="text-2xl font-bold text-warning mt-1">{{ $stats['openIssues'] }}</p>
                </div>
                <div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-2xl p-6 border border-surface-high">
        <h3 class="text-lg font-semibold text-text-primary mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <a href="{{ route('supervisor.tasks') }}"
               class="flex items-center gap-3 p-4 rounded-xl border border-surface-high hover:border-info hover:bg-info/5 transition-all">
                <div class="w-10 h-10 bg-info/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-text-primary">Tasks</p>
                    <p class="text-xs text-text-muted">Manage tasks</p>
                </div>
            </a>

            <a href="{{ route('supervisor.issues.index') }}"
               class="flex items-center gap-3 p-4 rounded-xl border border-surface-high hover:border-warning hover:bg-warning/5 transition-all">
                <div class="w-10 h-10 bg-warning/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-text-primary">Issues</p>
                    <p class="text-xs text-text-muted">Review reports</p>
                </div>
            </a>

            <a href="{{ route('supervisor.scan-qr') }}"
               class="flex items-center gap-3 p-4 rounded-xl border border-surface-high hover:border-primary hover:bg-primary/5 transition-all">
                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-text-primary">Scan QR</p>
                    <p class="text-xs text-text-muted">Setup QR codes</p>
                </div>
            </a>

            <a href="{{ route('supervisor.workers') }}"
               class="flex items-center gap-3 p-4 rounded-xl border border-surface-high hover:border-success hover:bg-success/5 transition-all">
                <div class="w-10 h-10 bg-success/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-text-primary">Workers</p>
                    <p class="text-xs text-text-muted">Manage team</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Tasks --}}
        <div class="bg-white rounded-2xl border border-surface-high">
            <div class="px-6 py-4 border-b border-surface-high flex items-center justify-between">
                <h3 class="text-sm font-semibold text-text-primary">Recent Tasks</h3>
                <a href="{{ route('supervisor.tasks') }}" class="text-xs text-primary hover:underline">View all</a>
            </div>
            <div class="divide-y divide-surface-high/50">
                @forelse ($recentTasks as $task)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-text-primary truncate">{{ $task->title }}</p>
                            <p class="text-xs text-text-muted mt-0.5">
                                {{ $task->assignee->name ?? 'Unassigned' }}
                                @if ($task->location_id && $task->location && is_object($task->location)) &middot; {{ $task->location->name }}
                                @elseif ($task->location) &middot; {{ $task->location }}
                                @endif
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ match($task->status->value) {
                                'pending' => 'bg-warning/10 text-warning',
                                'in_progress' => 'bg-info/10 text-info',
                                'completed' => 'bg-success/10 text-success',
                                'verified' => 'bg-success/10 text-success',
                                default => 'bg-surface-low text-text-muted',
                            } }}">
                            {{ $task->status->label() }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-text-muted">No tasks yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Issues --}}
        <div class="bg-white rounded-2xl border border-surface-high">
            <div class="px-6 py-4 border-b border-surface-high flex items-center justify-between">
                <h3 class="text-sm font-semibold text-text-primary">Recent Issues</h3>
                <a href="{{ route('supervisor.issues.index') }}" class="text-xs text-primary hover:underline">View all</a>
            </div>
            <div class="divide-y divide-surface-high/50">
                @forelse ($recentIssues as $issue)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono text-text-muted">{{ $issue->tracking_code }}</span>
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
                            </div>
                            <p class="text-sm text-text-primary mt-0.5 truncate">{{ Str::limit($issue->description, 60) }}</p>
                            <p class="text-xs text-text-muted mt-0.5">{{ $issue->location->name ?? 'Unknown' }} &middot; {{ $issue->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-text-muted">No issues yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
