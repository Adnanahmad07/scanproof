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
            <h2 class="text-lg font-bold text-text-primary">Tasks</h2>
            <p class="text-sm text-text-muted mt-0.5">Create and manage tasks for your workers</p>
        </div>
        <button wire:click="$set('showCreateModal', true)"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Task
        </button>
    </div>

    {{-- Tasks Table --}}
    <div class="bg-white rounded-2xl border border-surface-high overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-high bg-surface-low/50">
                        <th class="text-left px-6 py-4 font-semibold text-text-primary">Task</th>
                        <th class="text-left px-6 py-4 font-semibold text-text-primary">Assigned To</th>
                        <th class="text-left px-6 py-4 font-semibold text-text-primary">Status</th>
                        <th class="text-left px-6 py-4 font-semibold text-text-primary">Priority</th>
                        <th class="text-right px-6 py-4 font-semibold text-text-primary">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-high">
                    @forelse ($tasks as $task)
                        <tr class="hover:bg-surface-low/30 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-text-primary">{{ $task->title }}</p>
                                    <p class="text-xs text-text-muted">{{ $task->location }} &middot; {{ $task->category->label() }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($task->assignee)
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-success/10 rounded-lg flex items-center justify-center text-success font-semibold text-xs">
                                            {{ substr($task->assignee->name, 0, 2) }}
                                        </div>
                                        <span class="text-text-secondary">{{ $task->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="text-text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    @if($task->status->value === 'pending') bg-warning/10 text-warning
                                    @elseif($task->status->value === 'in_progress') bg-info/10 text-info
                                    @elseif($task->status->value === 'completed') bg-success/10 text-success
                                    @else bg-error/10 text-error
                                    @endif">
                                    {{ $task->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    @if($task->priority->value === 'urgent') bg-error/10 text-error
                                    @elseif($task->priority->value === 'high') bg-warning/10 text-warning
                                    @elseif($task->priority->value === 'medium') bg-info/10 text-info
                                    @else bg-success/10 text-success
                                    @endif">
                                    {{ $task->priority->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button wire:click="confirmDelete({{ $task->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-error text-xs font-semibold hover:bg-error/5 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-surface-low rounded-2xl flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                    </div>
                                    <p class="text-text-muted text-sm font-medium">No tasks yet</p>
                                    <p class="text-text-muted text-xs mt-1">Click "Create Task" to get started</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tasks->hasPages())
            <div class="px-6 py-4 border-t border-surface-high">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>

    {{-- Create Task Modal --}}
    <div x-data x-show="$wire.showCreateModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="$wire.set('showCreateModal', false)"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-text-primary">Create Task</h3>
                    <button @click="$wire.set('showCreateModal', false)" class="p-1 text-text-muted hover:text-text-primary rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="createTask" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-1.5">Title</label>
                        <input type="text" wire:model="taskTitle"
                               class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm"
                               placeholder="Task title">
                        @error('taskTitle') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-1.5">Description</label>
                        <textarea wire:model="taskDescription" rows="2"
                                  class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm"
                                  placeholder="Optional description"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Location</label>
                            <input type="text" wire:model="taskLocation"
                                   class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm"
                                   placeholder="e.g. Room 101">
                            @error('taskLocation') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Assign To</label>
                            <select wire:model="taskAssignedTo"
                                    class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm">
                                <option value="">Select worker</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                @endforeach
                            </select>
                            @error('taskAssignedTo') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Category</label>
                            <select wire:model="taskCategory"
                                    class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm">
                                <option value="cleaning">Cleaning</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="inspection">Inspection</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Priority</label>
                            <select wire:model="taskPriority"
                                    class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Due Date</label>
                            <input type="date" wire:model="taskDueDate"
                                   class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm">
                            @error('taskDueDate') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="$wire.set('showCreateModal', false)"
                                class="flex-1 px-4 py-2.5 border border-surface-high text-text-secondary rounded-xl text-sm font-semibold hover:bg-surface-low transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm">
                            Create Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-data x-show="$wire.taskToDelete" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="$wire.set('taskToDelete', null)"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 bg-error/10 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-text-primary mb-2">Delete Task</h3>
                    <p class="text-sm text-text-muted mb-6">Are you sure you want to delete this task? This action cannot be undone.</p>
                    <div class="flex gap-3 w-full">
                        <button @click="$wire.set('taskToDelete', null)"
                                class="flex-1 px-4 py-2.5 border border-surface-high text-text-secondary rounded-xl text-sm font-semibold hover:bg-surface-low transition-colors">
                            Cancel
                        </button>
                        <button wire:click="deleteTask"
                                class="flex-1 px-4 py-2.5 bg-error text-white rounded-xl text-sm font-semibold hover:bg-error/90 transition-colors shadow-sm">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
