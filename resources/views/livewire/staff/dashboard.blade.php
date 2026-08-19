<div class="space-y-6">

    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-r from-success to-success/80 rounded-2xl p-6 text-white">
        @if(auth()->user()->organization)
            <p class="text-white/90 text-sm font-semibold tracking-wide uppercase mb-1">{{ auth()->user()->organization->name }}</p>
        @endif
        <h2 class="text-2xl font-extrabold tracking-tight">Welcome, {{ auth()->user()->name }}!</h2>
        <p class="mt-1 text-white/80 text-sm">Here's your work overview for today.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-surface-high">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-text-muted">Total Tasks</p>
                    <p class="text-2xl font-bold text-text-primary mt-1">{{ $totalTasks }}</p>
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
                    <p class="text-sm text-text-muted">Pending</p>
                    <p class="text-2xl font-bold text-warning mt-1">{{ $pendingTasks }}</p>
                </div>
                <div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-surface-high">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-text-muted">In Progress</p>
                    <p class="text-2xl font-bold text-info mt-1">{{ $inProgressTasks }}</p>
                </div>
                <div class="w-12 h-12 bg-info/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-surface-high">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-text-muted">Completed</p>
                    <p class="text-2xl font-bold text-success mt-1">{{ $completedTasks }}</p>
                </div>
                <div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-2xl p-6 border border-surface-high">
        <h3 class="text-lg font-semibold text-text-primary mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('staff.scan') }}"
               class="flex items-center gap-3 p-4 rounded-xl border border-surface-high hover:border-success hover:bg-success/5 transition-all">
                <div class="w-10 h-10 bg-success/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-text-primary">Scan QR Code</p>
                    <p class="text-xs text-text-muted">Scan a facility QR</p>
                </div>
            </a>

            <a href="{{ route('staff.tasks') }}"
               class="flex items-center gap-3 p-4 rounded-xl border border-surface-high hover:border-info hover:bg-info/5 transition-all">
                <div class="w-10 h-10 bg-info/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-text-primary">View Tasks</p>
                    <p class="text-xs text-text-muted">See your assigned tasks</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Recent Tasks --}}
    <div class="bg-white rounded-2xl border border-surface-high overflow-hidden">
        <div class="px-6 py-4 border-b border-surface-high">
            <h3 class="text-lg font-semibold text-text-primary">Recent Tasks</h3>
        </div>
        <div class="divide-y divide-surface-high">
            @forelse ($recentTasks as $task)
                <div class="px-6 py-4 hover:bg-surface-low/30 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-text-primary truncate">{{ $task->title }}</p>
                            <p class="text-xs text-text-muted mt-0.5">{{ $task->location }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            @if($task->status->value === 'pending') bg-warning/10 text-warning
                            @elseif($task->status->value === 'in_progress') bg-info/10 text-info
                            @elseif($task->status->value === 'completed') bg-success/10 text-success
                            @else bg-error/10 text-error
                            @endif">
                            {{ $task->status->label() }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center">
                    <p class="text-text-muted text-sm">No tasks assigned yet</p>
                </div>
            @endforelse
        </div>
    </div>

</div>
