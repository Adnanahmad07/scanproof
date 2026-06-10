<div>
    {{-- Success Message --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
            {{ session('success') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Supervisors</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your facility supervisors.</p>
        </div>
        <button wire:click="$set('showInviteModal', true)"
                type="button"
                class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm cursor-pointer">
            Invite Supervisor
        </button>
    </div>

    {{-- Active Supervisors --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Active Supervisors</h2>

        @if ($supervisors->isEmpty())
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-gray-400 text-sm">No supervisors yet. Send an invitation to get started.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Name</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Email</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Joined</th>
                            <th class="text-right py-3 px-4 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supervisors as $supervisor)
                            <tr class="border-b border-gray-200 last:border-0 hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                            <span class="text-xs font-semibold text-blue-600">{{ substr($supervisor->name, 0, 2) }}</span>
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $supervisor->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-500">{{ $supervisor->email }}</td>
                                <td class="py-3 px-4 text-gray-400">{{ $supervisor->created_at->diffForHumans() }}</td>
                                <td class="py-3 px-4 text-right">
                                    <button wire:click="confirmDelete({{ $supervisor->id }})"
                                            type="button"
                                            class="text-gray-400 hover:text-red-600 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $supervisors->links() }}
            </div>
        @endif
    </div>

    {{-- Pending Invitations --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Pending Invitations</h2>

        @if ($invitations->isEmpty())
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-400 text-sm">No pending invitations.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Email</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Status</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Expires</th>
                            <th class="text-right py-3 px-4 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invitations as $invitation)
                            <tr class="border-b border-gray-200 last:border-0 hover:bg-gray-50">
                                <td class="py-3 px-4 text-gray-900">{{ $invitation->email }}</td>
                                <td class="py-3 px-4">
                                    @if ($invitation->status === 'pending')
                                        <span class="text-xs font-medium text-yellow-700 bg-yellow-50 px-2.5 py-1 rounded-full">Pending</span>
                                    @elseif ($invitation->status === 'accepted')
                                        <span class="text-xs font-medium text-green-700 bg-green-50 px-2.5 py-1 rounded-full">Accepted</span>
                                    @else
                                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">Expired</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-400">{{ $invitation->expires_at->diffForHumans() }}</td>
                                <td class="py-3 px-4 text-right">
                                    @if ($invitation->status === 'pending')
                                        <button wire:click="cancelInvitation({{ $invitation->id }})"
                                                type="button"
                                                class="text-gray-400 hover:text-red-600 transition-colors cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $invitations->links() }}
            </div>
        @endif
    </div>

    {{-- Invite Modal --}}
    <div x-data="{ open: @entangle('showInviteModal') }" x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-on:click="open = false; $wire.set('showInviteModal', false)"></div>

            <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Invite Supervisor</h3>

                    <form wire:submit="invite">
                        <div>
                            <label for="inviteEmail" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email"
                                   wire:model="inviteEmail"
                                   id="inviteEmail"
                                   class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                                   placeholder="supervisor@example.com">
                            @error('inviteEmail')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:w-auto sm:text-sm">
                                Send Invitation
                            </button>
                            <button type="button"
                                    x-on:click="open = false; $wire.set('showInviteModal', false)"
                                    class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-data="{ open: @entangle('supervisorToDelete') }" x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-on:click="$wire.set('supervisorToDelete', null)"></div>

            <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Demote Supervisor</h3>
                    <p class="text-sm text-gray-500">Are you sure you want to demote this supervisor to staff? They will lose access to supervisor features.</p>

                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                        <button wire:click="deleteSupervisor"
                                type="button"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:w-auto sm:text-sm">
                            Demote
                        </button>
                        <button type="button"
                                x-on:click="$wire.set('supervisorToDelete', null)"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
