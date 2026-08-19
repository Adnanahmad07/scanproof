<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-xl font-bold text-text-primary">Issues</h1>
        <p class="text-xs text-text-secondary mt-0.5">Manage and assign issues reported from your locations</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-surface-high p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-warning/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-warning">{{ $stats['reported'] }}</p>
                    <p class="text-xs text-text-muted">New Reports</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-surface-high p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-info/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-info">{{ $stats['assigned'] }}</p>
                    <p class="text-xs text-text-muted">Assigned</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-surface-high p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-primary">{{ $stats['in_progress'] }}</p>
                    <p class="text-xs text-text-muted">In Progress</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-surface-high p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-success/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-success">{{ $stats['resolved'] }}</p>
                    <p class="text-xs text-text-muted">Resolved</p>
                </div>
            </div>
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
                <p class="text-text-muted text-sm mt-1">Issues reported from your locations will appear here.</p>
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
                                            'rejected' => 'bg-error/10 text-error',
                                            default => 'bg-surface-low text-text-muted',
                                        } }}">
                                        {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                                    </span>
                                    <span class="text-xs font-mono text-text-muted">{{ $issue->tracking_code }}</span>
                                </div>

                                {{-- Description --}}
                                <p class="text-sm text-text-primary line-clamp-2">{{ $issue->description }}</p>

                                {{-- Meta --}}
                                <div class="flex items-center gap-3 mt-2 text-xs text-text-muted">
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

                            {{-- Actions --}}
                            @if ($issue->status !== 'resolved' && $issue->status !== 'rejected')
                                <div class="flex items-center gap-2 shrink-0">
                                    {{-- Assign button --}}
                                    @if ($issue->status === 'reported')
                                        <div x-data="{ showAssign: false }">
                                            <button @click="showAssign = true"
                                                    class="px-3 py-1.5 text-xs font-medium text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors">
                                                Assign
                                            </button>
                                            <div x-show="showAssign" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                                <div class="absolute inset-0 bg-black/50" @click="showAssign = false"></div>
                                                <div class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
                                                    <h3 class="text-lg font-semibold text-text-primary mb-4">Assign Worker</h3>
                                                    <form method="POST" action="{{ route('supervisor.issues.assign', $issue->id) }}">
                                                        @csrf
                                                        <select name="assigned_to" required
                                                                class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary mb-4">
                                                            <option value="">Select worker...</option>
                                                            @foreach (\App\Models\User::where('role', 'staff')->where('organization_id', auth()->user()->organization_id)->orderBy('name')->get() as $worker)
                                                                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="flex justify-end gap-3">
                                                            <button type="button" @click="showAssign = false" class="text-sm text-text-muted hover:text-text-primary">Cancel</button>
                                                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90">Assign</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Convert to Task button --}}
                                    @if ($issue->assigned_to && !$issue->task)
                                        <div x-data="{ showConvert: false }">
                                            <button @click="showConvert = true"
                                                    class="px-3 py-1.5 text-xs font-medium text-success bg-success/10 rounded-lg hover:bg-success/20 transition-colors">
                                                Convert to Task
                                            </button>
                                            <div x-show="showConvert" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                                <div class="absolute inset-0 bg-black/50" @click="showConvert = false"></div>
                                                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                                                    <h3 class="text-lg font-semibold text-text-primary mb-2">Convert to Task</h3>
                                                    <p class="text-sm text-text-secondary mb-4">Create a task from this issue and assign it to {{ $issue->assignee->name ?? 'the worker' }}.</p>
                                                    <form method="POST" action="{{ route('supervisor.issues.convert-to-task', $issue->id) }}">
                                                        @csrf
                                                        <div class="space-y-4">
                                                            <div>
                                                                <label for="category" class="block text-sm font-medium text-text-primary mb-1">Category</label>
                                                                <select name="category" id="category" required
                                                                        class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                                                    <option value="cleaning">Cleaning</option>
                                                                    <option value="maintenance" selected>Maintenance</option>
                                                                    <option value="inspection">Inspection</option>
                                                                    <option value="other">Other</option>
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label for="priority" class="block text-sm font-medium text-text-primary mb-1">Priority</label>
                                                                <select name="priority" id="priority" required
                                                                        class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                                                    <option value="low">Low</option>
                                                                    <option value="medium" selected>Medium</option>
                                                                    <option value="high">High</option>
                                                                    <option value="urgent">Urgent</option>
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label for="due_date" class="block text-sm font-medium text-text-primary mb-1">Due Date (optional)</label>
                                                                <input type="date" name="due_date" id="due_date"
                                                                       min="{{ now()->addDay()->format('Y-m-d') }}"
                                                                       class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                                            </div>
                                                        </div>
                                                        <div class="flex justify-end gap-3 mt-6">
                                                            <button type="button" @click="showConvert = false" class="text-sm text-text-muted hover:text-text-primary">Cancel</button>
                                                            <button type="submit" class="px-4 py-2 bg-success text-white rounded-xl text-sm font-medium hover:bg-success/90">Convert</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Reject button --}}
                                    @if ($issue->status !== 'rejected')
                                        <div x-data="{ showReject: false }">
                                            <button @click="showReject = true"
                                                    class="px-3 py-1.5 text-xs font-medium text-error bg-error/10 rounded-lg hover:bg-error/20 transition-colors">
                                                Reject
                                            </button>
                                            <div x-show="showReject" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                                <div class="absolute inset-0 bg-black/50" @click="showReject = false"></div>
                                                <div class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
                                                    <h3 class="text-lg font-semibold text-text-primary mb-2">Reject Issue</h3>
                                                    <p class="text-sm text-text-secondary mb-4">Provide a reason for rejecting this issue.</p>
                                                    <form method="POST" action="{{ route('supervisor.issues.reject', $issue->id) }}">
                                                        @csrf
                                                        <textarea name="rejection_reason" rows="3" required minlength="5" maxlength="500"
                                                                  class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary mb-4"
                                                                  placeholder="Reason for rejection..."></textarea>
                                                        <div class="flex justify-end gap-3">
                                                            <button type="button" @click="showReject = false" class="text-sm text-text-muted hover:text-text-primary">Cancel</button>
                                                            <button type="submit" class="px-4 py-2 bg-error text-white rounded-xl text-sm font-medium hover:bg-error/90">Reject</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
