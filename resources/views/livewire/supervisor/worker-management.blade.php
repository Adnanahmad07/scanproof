<div>
    {{-- Success Flash Message --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mb-6 p-4 bg-success/10 border border-success/20 rounded-xl text-success text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-text-primary">My Workers</h2>
            <p class="text-sm text-text-muted mt-0.5">Manage your team members</p>
        </div>
        <button wire:click="$set('showAddModal', true)"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Worker
        </button>
    </div>

    {{-- Workers Table --}}
    <div class="bg-white rounded-2xl border border-surface-high overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-high bg-surface-low/50">
                        <th class="text-left px-6 py-4 font-semibold text-text-primary">Worker</th>
                        <th class="text-left px-6 py-4 font-semibold text-text-primary">Email</th>
                        <th class="text-left px-6 py-4 font-semibold text-text-primary">Joined</th>
                        <th class="text-right px-6 py-4 font-semibold text-text-primary">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-high">
                    @forelse ($workers as $worker)
                        <tr class="hover:bg-surface-low/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-success/10 rounded-xl flex items-center justify-center text-success font-semibold text-sm shrink-0">
                                        {{ substr($worker->name, 0, 2) }}
                                    </div>
                                    <span class="font-medium text-text-primary">{{ $worker->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-text-secondary">{{ $worker->email }}</td>
                            <td class="px-6 py-4 text-text-muted">{{ $worker->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <button wire:click="confirmDelete({{ $worker->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-error text-xs font-semibold hover:bg-error/5 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-surface-low rounded-2xl flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-text-muted text-sm font-medium">No workers yet</p>
                                    <p class="text-text-muted text-xs mt-1">Click "Add Worker" to get started</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($workers->hasPages())
            <div class="px-6 py-4 border-t border-surface-high">
                {{ $workers->links() }}
            </div>
        @endif
    </div>

    {{-- Add Worker Modal --}}
    <div x-data x-show="$wire.showAddModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="$wire.set('showAddModal', false)"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-text-primary">Add Worker</h3>
                    <button @click="$wire.set('showAddModal', false)" class="p-1 text-text-muted hover:text-text-primary rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="addWorker" class="space-y-4">
                    <div>
                        <label for="workerName" class="block text-sm font-semibold text-text-primary mb-1.5">Name</label>
                        <input type="text" id="workerName" wire:model="workerName"
                               class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm"
                               placeholder="Enter worker name">
                        @error('workerName') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="workerEmail" class="block text-sm font-semibold text-text-primary mb-1.5">Email</label>
                        <input type="email" id="workerEmail" wire:model="workerEmail"
                               class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm"
                               placeholder="worker@example.com">
                        @error('workerEmail') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="workerPassword" class="block text-sm font-semibold text-text-primary mb-1.5">Password</label>
                        <input type="password" id="workerPassword" wire:model="workerPassword"
                               class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm"
                               placeholder="Min 8 characters">
                        @error('workerPassword') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="$wire.set('showAddModal', false)"
                                class="flex-1 px-4 py-2.5 border border-surface-high text-text-secondary rounded-xl text-sm font-semibold hover:bg-surface-low transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm">
                            Add Worker
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-data x-show="$wire.workerToDelete" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="$wire.set('workerToDelete', null)"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 bg-error/10 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-text-primary mb-2">Remove Worker</h3>
                    <p class="text-sm text-text-muted mb-6">Are you sure you want to remove this worker from your team? They will no longer be assigned to you.</p>
                    <div class="flex gap-3 w-full">
                        <button @click="$wire.set('workerToDelete', null)"
                                class="flex-1 px-4 py-2.5 border border-surface-high text-text-secondary rounded-xl text-sm font-semibold hover:bg-surface-low transition-colors">
                            Cancel
                        </button>
                        <button wire:click="deleteWorker"
                                class="flex-1 px-4 py-2.5 bg-error text-white rounded-xl text-sm font-semibold hover:bg-error/90 transition-colors shadow-sm">
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
