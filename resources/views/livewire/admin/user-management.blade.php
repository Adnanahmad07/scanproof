<div>
    {{-- Success Message --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            {{ session('error') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Users</h1>
            <p class="text-sm text-gray-500 mt-1">Manage all system users, roles, and access.</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="openCreateModal"
                    type="button"
                    class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm cursor-pointer">
                Add Worker
            </button>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        @if ($users->isEmpty())
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-gray-400 text-sm">No users found.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Name</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Email</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Role</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Status</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500 hidden sm:table-cell">Joined</th>
                            <th class="text-right py-3 px-4 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border-b border-gray-200 last:border-0 hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                            <span class="text-xs font-semibold text-blue-600">{{ substr($user->name, 0, 2) }}</span>
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-500">{{ $user->email }}</td>
                                <td class="py-3 px-4">
                                    @if ($user->role->value === 'admin')
                                        <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full">Admin</span>
                                    @elseif ($user->role->value === 'supervisor')
                                        <span class="text-xs font-medium text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full">Supervisor</span>
                                    @elseif ($user->role->value === 'staff')
                                        <span class="text-xs font-medium text-green-700 bg-green-50 px-2.5 py-1 rounded-full">Staff</span>
                                    @else
                                        <span class="text-xs font-medium text-yellow-700 bg-yellow-50 px-2.5 py-1 rounded-full">Client</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if ($user->is_active)
                                        <span class="text-xs font-medium text-green-700 bg-green-50 px-2.5 py-1 rounded-full">Active</span>
                                    @else
                                        <span class="text-xs font-medium text-red-700 bg-red-50 px-2.5 py-1 rounded-full">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-400 hidden sm:table-cell">{{ $user->created_at->diffForHumans() }}</td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="openEditModal({{ $user->id }})"
                                                type="button"
                                                class="text-gray-400 hover:text-blue-600 transition-colors cursor-pointer"
                                                title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        @if($user->role->value === 'staff')
                                            <button wire:click="promoteToSupervisor({{ $user->id }})"
                                                    type="button"
                                                    class="text-gray-400 hover:text-indigo-600 transition-colors cursor-pointer"
                                                    title="Promote to Supervisor">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                                </svg>
                                            </button>
                                        @elseif($user->role->value === 'supervisor')
                                            <button wire:click="demoteToStaff({{ $user->id }})"
                                                    type="button"
                                                    class="text-gray-400 hover:text-yellow-600 transition-colors cursor-pointer"
                                                    title="Demote to Staff">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                </svg>
                                            </button>
                                        @endif
                                        <button wire:click="toggleActive({{ $user->id }})"
                                                type="button"
                                                class="text-gray-400 hover:text-{{ $user->is_active ? 'red' : 'green' }}-600 transition-colors cursor-pointer"
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                            @if ($user->is_active)
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Create User Modal --}}
    <div x-data="{ open: @entangle('showCreateModal') }" x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-on:click="$wire.call('closeCreateModal')"></div>

            <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Worker</h3>

                    <form wire:submit="createUser">
                        <div class="space-y-4">
                            <div>
                                <label for="createName" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input type="text"
                                       wire:model="createName"
                                       id="createName"
                                       class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                                       placeholder="Full name">
                                @error('createName')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="createEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email"
                                       wire:model="createEmail"
                                       id="createEmail"
                                       class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                                       placeholder="user@example.com">
                                @error('createEmail')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="createPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password"
                                       wire:model="createPassword"
                                       id="createPassword"
                                       class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                                       placeholder="Min 8 characters">
                                @error('createPassword')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="createPasswordConfirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <input type="password"
                                       wire:model="createPassword_confirmation"
                                       id="createPasswordConfirmation"
                                       class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                                       placeholder="Repeat password">
                            </div>

                            <div>
                                <label for="createSupervisorId" class="block text-sm font-medium text-gray-700 mb-1">Assign to Supervisor (optional)</label>
                                <select wire:model="createSupervisorId"
                                        id="createSupervisorId"
                                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                                    <option value="">No supervisor</option>
                                    @foreach ($supervisors as $supervisor)
                                        <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-400">Link this worker to a supervisor's team.</p>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:w-auto sm:text-sm">
                                Add Worker
                            </button>
                            <button type="button"
                                    x-on:click="$wire.call('closeCreateModal')"
                                    class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit User Modal --}}
    <div x-data="{ open: @entangle('showEditModal') }" x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-on:click="$wire.call('closeEditModal')"></div>

            <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit User</h3>

                    <form wire:submit="updateUser">
                        <div class="space-y-4">
                            <div>
                                <label for="editName" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input type="text"
                                       wire:model="editName"
                                       id="editName"
                                       class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                                @error('editName')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="editEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email"
                                       wire:model="editEmail"
                                       id="editEmail"
                                       class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                                @error('editEmail')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="editRole" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <select wire:model="editRole"
                                        id="editRole"
                                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                                    <option value="staff">Staff</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="admin">Admin</option>
                                    <option value="client">Client</option>
                                </select>
                                @error('editRole')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $editIsActive ? 'bg-blue-600' : 'bg-gray-200' }} cursor-pointer">
                                    <input type="checkbox" wire:model="editIsActive" class="sr-only">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $editIsActive ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </label>
                                <span class="text-sm text-gray-700">Active</span>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:w-auto sm:text-sm">
                                Save Changes
                            </button>
                            <button type="button"
                                    x-on:click="$wire.call('closeEditModal')"
                                    class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
