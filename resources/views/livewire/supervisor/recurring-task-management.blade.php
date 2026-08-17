<div>
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mb-6 p-4 bg-success/10 border border-success/20 rounded-xl text-success text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-text-primary">Recurring Tasks</h2>
            <p class="text-sm text-text-muted mt-0.5">Set up daily, weekly, or monthly recurring tasks like floor cleaning</p>
        </div>
        <button wire:click="openCreateModal"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Recurring Task
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-surface-high overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-high bg-surface-low/50">
                        <th class="text-left px-6 py-4 font-semibold text-text-primary">Task</th>
                        <th class="text-left px-6 py-4 font-semibold text-text-primary">Schedule</th>
                        <th class="text-left px-6 py-4 font-semibold text-text-primary">Assigned To</th>
                        <th class="text-left px-6 py-4 font-semibold text-text-primary">Status</th>
                        <th class="text-right px-6 py-4 font-semibold text-text-primary">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-high">
                    @forelse ($recurringTasks as $task)
                        <tr class="hover:bg-surface-low/30 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-text-primary">{{ $task->title }}</p>
                                    <p class="text-xs text-text-muted">{{ $task->location }} &middot; {{ $task->category->label() }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                                    {{ ucfirst($task->frequency) }}
                                </span>
                                @if($task->frequency === 'weekly' && $task->day_of_week !== null)
                                    <p class="text-xs text-text-muted mt-1">{{ ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$task->day_of_week] }}</p>
                                @elseif($task->frequency === 'monthly' && $task->day_of_month !== null)
                                    <p class="text-xs text-text-muted mt-1">Day {{ $task->day_of_month }}</p>
                                @endif
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
                                <button wire:click="toggleActive({{ $task->id }})"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold cursor-pointer
                                        {{ $task->is_active ? 'bg-success/10 text-success' : 'bg-surface-low text-text-muted' }}">
                                    {{ $task->is_active ? 'Active' : 'Paused' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="editTask({{ $task->id }})"
                                            class="p-1.5 text-text-muted hover:text-primary hover:bg-primary/5 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $task->id }})"
                                            class="p-1.5 text-text-muted hover:text-error hover:bg-error/5 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-surface-low rounded-2xl flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </div>
                                    <p class="text-text-muted text-sm font-medium">No recurring tasks yet</p>
                                    <p class="text-text-muted text-xs mt-1">Create one to automate daily cleaning schedules</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <div x-data x-show="$wire.showCreateModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="$wire.set('showCreateModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-text-primary" x-text="$wire.editingId ? 'Edit Recurring Task' : 'Create Recurring Task'"></h3>
                    <button @click="$wire.set('showCreateModal', false)" class="p-1 text-text-muted hover:text-text-primary rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveTask" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-1.5">Title</label>
                        <input type="text" wire:model="title"
                               class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm"
                               placeholder="e.g. Floor Cleaning">
                        @error('title') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-primary mb-1.5">Description</label>
                        <textarea wire:model="description" rows="2"
                                  class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm"
                                  placeholder="Optional description"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Location</label>
                            <select wire:model="locationId"
                                    class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm">
                                <option value="">Select location</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}{{ $loc->building ? ' - '.$loc->building : '' }}</option>
                                @endforeach
                            </select>
                            @error('locationId') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Assign To</label>
                            <select wire:model="assignedTo"
                                    class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm">
                                <option value="">Select worker</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                @endforeach
                            </select>
                            @error('assignedTo') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Category</label>
                            <select wire:model="category"
                                    class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm">
                                <option value="cleaning">Cleaning</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="inspection">Inspection</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Priority</label>
                            <select wire:model="priority"
                                    class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Frequency</label>
                            <select wire:model="frequency"
                                    class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>

                    @if($frequency === 'weekly')
                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Day of Week</label>
                            <select wire:model="dayOfWeek"
                                    class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm">
                                <option value="0">Sunday</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                            </select>
                        </div>
                    @endif

                    @if($frequency === 'monthly')
                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Day of Month</label>
                            <input type="number" wire:model="dayOfMonth" min="1" max="31"
                                   class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm">
                        </div>
                    @endif

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="$wire.set('showCreateModal', false)"
                                class="flex-1 px-4 py-2.5 border border-surface-high text-text-secondary rounded-xl text-sm font-semibold hover:bg-surface-low transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm">
                            <span x-text="$wire.editingId ? 'Update' : 'Create'"></span>
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
                    <h3 class="text-lg font-bold text-text-primary mb-2">Deactivate Recurring Task</h3>
                    <p class="text-sm text-text-muted mb-6">This will stop generating new tasks from this template.</p>
                    <div class="flex gap-3 w-full">
                        <button @click="$wire.set('taskToDelete', null)"
                                class="flex-1 px-4 py-2.5 border border-surface-high text-text-secondary rounded-xl text-sm font-semibold hover:bg-surface-low transition-colors">
                            Cancel
                        </button>
                        <button wire:click="deleteTask"
                                class="flex-1 px-4 py-2.5 bg-error text-white rounded-xl text-sm font-semibold hover:bg-error/90 transition-colors shadow-sm">
                            Deactivate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
