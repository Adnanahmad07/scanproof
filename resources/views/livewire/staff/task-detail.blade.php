<div>
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mb-4 p-4 bg-success/10 border border-success/20 rounded-xl text-success text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if($task)
        <div class="space-y-6">
            {{-- Task Info --}}
            <div class="bg-white rounded-2xl border border-surface-high p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-text-primary">{{ $task->title }}</h3>
                        <p class="text-sm text-text-muted mt-1">{{ is_object($task->location) ? $task->location->name : $task->location }} &middot; {{ $task->category->label() }}</p>
                        @if($task->supervisor)
                            <p class="text-sm text-text-muted mt-1">Supervisor: <span class="font-medium text-text-primary">{{ $task->supervisor->name }}</span></p>
                        @endif
                        @if($task->due_date)
                            <p class="text-sm text-text-muted mt-1">Due: {{ $task->due_date->format('M d, Y') }}</p>
                        @endif
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                        @if($task->status->value === 'pending') bg-warning/10 text-warning
                        @elseif($task->status->value === 'in_progress') bg-info/10 text-info
                        @elseif($task->status->value === 'completed') bg-success/10 text-success
                        @else bg-error/10 text-error
                        @endif">
                        {{ $task->status->label() }}
                    </span>
                </div>
                @if($task->description)
                    <p class="text-sm text-text-secondary mt-3">{{ $task->description }}</p>
                @endif

                <div class="flex gap-3 mt-4">
                    @if($task->status->value === 'pending')
                        <button wire:click="startTask"
                                class="px-4 py-2 bg-info text-white rounded-xl text-sm font-semibold hover:bg-info/90 transition-colors">
                            Start Task
                        </button>
                    @elseif($task->status->value === 'in_progress')
                        <button wire:click="completeTask"
                                class="px-4 py-2 bg-success text-white rounded-xl text-sm font-semibold hover:bg-success/90 transition-colors">
                            Complete Task
                        </button>
                    @endif
                    <a href="{{ route('staff.tasks') }}"
                       class="px-4 py-2 border border-surface-high text-text-secondary rounded-xl text-sm font-semibold hover:bg-surface-low transition-colors">
                        Back to Tasks
                    </a>
                </div>
            </div>

            {{-- Photo Upload Section --}}
            <livewire:staff.task-photo-upload :taskId="$task->id" :key="$task->id" />
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-text-muted">Task not found or you don't have access.</p>
            <a href="{{ route('staff.tasks') }}" class="mt-4 inline-block text-primary text-sm font-medium hover:underline">Back to Tasks</a>
        </div>
    @endif
</div>
