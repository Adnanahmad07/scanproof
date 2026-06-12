<div>
    {{-- Success Flash Message --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mb-6 p-4 bg-success/10 border border-success/20 rounded-xl text-success text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-text-primary">My Tasks</h2>
            <p class="text-sm text-text-muted mt-0.5">Tasks assigned to you</p>
        </div>
        <div class="flex gap-2">
            <select wire:model="statusFilter"
                    class="px-4 py-2 bg-white border border-surface-high rounded-xl text-sm text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>

    {{-- Tasks List --}}
    <div class="space-y-3">
        @forelse ($tasks as $task)
            <div class="bg-white rounded-2xl border border-surface-high p-5 hover:shadow-sm transition-shadow">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    {{-- Task Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-sm font-semibold text-text-primary">{{ $task->title }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                @if($task->priority->value === 'urgent') bg-error/10 text-error
                                @elseif($task->priority->value === 'high') bg-warning/10 text-warning
                                @elseif($task->priority->value === 'medium') bg-info/10 text-info
                                @else bg-success/10 text-success
                                @endif">
                                {{ $task->priority->label() }}
                            </span>
                        </div>
                        <p class="text-xs text-text-muted">{{ $task->location }} &middot; {{ $task->category->label() }}</p>
                        @if($task->due_date)
                            <p class="text-xs text-text-muted mt-1">Due: {{ $task->due_date->format('M d, Y') }}</p>
                        @endif
                        @if($task->description)
                            <p class="text-xs text-text-secondary mt-2 line-clamp-2">{{ $task->description }}</p>
                        @endif
                    </div>

                    {{-- Status + Actions --}}
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            @if($task->status->value === 'pending') bg-warning/10 text-warning
                            @elseif($task->status->value === 'in_progress') bg-info/10 text-info
                            @elseif($task->status->value === 'completed') bg-success/10 text-success
                            @else bg-error/10 text-error
                            @endif">
                            {{ $task->status->label() }}
                        </span>

                        @if($task->status->value === 'pending')
                            <button wire:click="startTask({{ $task->id }})"
                                    class="px-3 py-1.5 bg-info text-white rounded-lg text-xs font-semibold hover:bg-info/90 transition-colors">
                                Start
                            </button>
                        @elseif($task->status->value === 'in_progress')
                            <button wire:click="completeTask({{ $task->id }})"
                                    class="px-3 py-1.5 bg-success text-white rounded-lg text-xs font-semibold hover:bg-success/90 transition-colors">
                                Complete
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-surface-high p-12 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-surface-low rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <p class="text-text-muted text-sm font-medium">No tasks found</p>
                    <p class="text-text-muted text-xs mt-1">Check back later for new assignments</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($tasks->hasPages())
        <div class="mt-6">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
