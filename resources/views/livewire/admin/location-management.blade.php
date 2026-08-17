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
            <h1 class="text-2xl font-bold text-gray-900">Locations</h1>
            <p class="text-sm text-gray-500 mt-1">Manage facility locations and QR codes.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button wire:click="openCsvModal"
                    type="button"
                    class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors shadow-sm cursor-pointer">
                Import CSV
            </button>
            <button wire:click="openCreateModal"
                    type="button"
                    class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm cursor-pointer">
                Add Location
            </button>
        </div>
    </div>

    {{-- Print Action Bar --}}
    @if (count($selectedForPrint) > 0)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <span class="text-sm font-medium text-blue-800">{{ count($selectedForPrint) }} location(s) selected</span>
            <div class="flex gap-2">
                <a href="{{ route('admin.locations.print', ['ids' => $selectedForPrint]) }}"
                   target="_blank"
                   class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">
                    Print Selected
                </a>
                <a href="{{ route('admin.locations.pdf', ['ids' => $selectedForPrint]) }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors">
                    Download PDF
                </a>
                <button wire:click="clearSelection"
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer">
                    Clear
                </button>
            </div>
        </div>
    @endif

    {{-- Locations Tree --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">All Locations</h2>
            @if ($allLocationsCount > 0)
                <div class="flex gap-2">
                    <a href="{{ route('admin.locations.print', ['all' => 1]) }}"
                       target="_blank"
                       class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Print All
                    </a>
                    <a href="{{ route('admin.locations.pdf', ['all' => 1]) }}"
                       class="px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        PDF All
                    </a>
                </div>
            @endif
        </div>

        @if ($locations->isEmpty())
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-gray-400 text-sm">No locations yet. Add one or import from CSV.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="w-10 py-3 px-2">
                                <input type="checkbox" wire:click="selectAll"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Name</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Type</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500">Building</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500 hidden sm:table-cell">Parent</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500 hidden md:table-cell">Supervisor</th>
                            <th class="text-right py-3 px-4 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($locations as $location)
                            <tr class="border-b border-gray-200 last:border-0 hover:bg-gray-50">
                                <td class="py-3 px-2">
                                    <input type="checkbox" value="{{ $location->id }}"
                                           wire:click="togglePrintSelection({{ $location->id }})"
                                           {{ in_array($location->id, $selectedForPrint) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-gray-100">
                                            <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $location->type->icon() }}" />
                                            </svg>
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $location->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-{{ $location->type->color() }}/10 text-{{ $location->type->color() }}">
                                        {{ $location->type->label() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-500">{{ $location->building ?? '—' }}</td>
                                <td class="py-3 px-4 text-gray-500 hidden sm:table-cell">{{ $location->parent?->name ?? '—' }}</td>
                                <td class="py-3 px-4 text-gray-500 hidden md:table-cell">{{ $location->supervisor?->name ?? '—' }}</td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="showQr({{ $location->id }})"
                                                type="button"
                                                class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                                title="View QR Code">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                            </svg>
                                        </button>
                                        <button wire:click="openEditModal({{ $location->id }})"
                                                type="button"
                                                class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors cursor-pointer"
                                                title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $location->id }})"
                                                type="button"
                                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                                                title="Delete">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @foreach ($location->children as $child)
                                <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                                    <td class="py-3 px-2 pl-8">
                                        <input type="checkbox" value="{{ $child->id }}"
                                               wire:click="togglePrintSelection({{ $child->id }})"
                                               {{ in_array($child->id, $selectedForPrint) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    </td>
                                    <td class="py-3 px-4 pl-12">
                                        <div class="flex items-center gap-3">
                                            <div class="w-6 h-6 rounded flex items-center justify-center bg-gray-50">
                                                <svg class="w-3 h-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $child->type->icon() }}" />
                                                </svg>
                                            </div>
                                            <span class="text-gray-700">{{ $child->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-{{ $child->type->color() }}/10 text-{{ $child->type->color() }}">
                                            {{ $child->type->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-500">{{ $child->building ?? '—' }}</td>
                                    <td class="py-3 px-4 text-gray-500 hidden sm:table-cell">{{ $child->parent?->name ?? '—' }}</td>
                                    <td class="py-3 px-4 text-gray-500 hidden md:table-cell">{{ $child->supervisor?->name ?? '—' }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="showQr({{ $child->id }})"
                                                    type="button"
                                                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                                </svg>
                                            </button>
                                            <button wire:click="openEditModal({{ $child->id }})"
                                                    type="button"
                                                    class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button wire:click="confirmDelete({{ $child->id }})"
                                                    type="button"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div x-data="{ open: @entangle('showCreateModal') }" x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-on:click="open = false; $wire.set('showCreateModal', false)"></div>
            <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Location</h3>
                    <form wire:submit="createLocation">
                        @include('livewire.admin._location-form')
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium text-sm">
                                Create
                            </button>
                            <button type="button" x-on:click="open = false; $wire.set('showCreateModal', false)"
                                    class="mt-3 sm:mt-0 w-full sm:w-auto px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-data="{ open: @entangle('showEditModal') }" x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-on:click="open = false; $wire.set('showEditModal', false)"></div>
            <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Location</h3>
                    <form wire:submit="updateLocation">
                        @include('livewire.admin._location-form')
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium text-sm">
                                Update
                            </button>
                            <button type="button" x-on:click="open = false; $wire.set('showEditModal', false)"
                                    class="mt-3 sm:mt-0 w-full sm:w-auto px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- CSV Import Modal --}}
    <div x-data="{ open: @entangle('showCsvModal') }" x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-on:click="open = false; $wire.set('showCsvModal', false)"></div>
            <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Import Locations from CSV</h3>
                    <p class="text-sm text-gray-500 mb-4">Required columns: <code>name</code>, <code>type</code>. Optional: <code>parent</code>, <code>building</code>, <code>floor</code>, <code>notes</code>.</p>

                    <form wire:submit="importCsv">
                        <div>
                            <input type="file" wire:model="csvFile" accept=".csv,.txt"
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            @error('csvFile')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if (!empty($importResult))
                            <div class="mt-4 p-3 rounded-xl {{ $importResult['created'] > 0 ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200' }}">
                                <p class="text-sm font-medium {{ $importResult['created'] > 0 ? 'text-green-800' : 'text-yellow-800' }}">
                                    {{ $importResult['created'] }} location(s) created.
                                    @if (count($importResult['skipped']) > 0)
                                        {{ count($importResult['skipped']) }} row(s) skipped.
                                    @endif
                                </p>
                                @foreach ($importResult['skipped'] as $skip)
                                    <p class="text-xs text-yellow-700 mt-1">Row {{ $skip['row'] }}: {{ $skip['reason'] }}</p>
                                @endforeach
                                @foreach ($importResult['errors'] as $err)
                                    <p class="text-xs text-red-600 mt-1">Row {{ $err['row'] }}: {{ $err['message'] }}</p>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium text-sm">
                                Import
                            </button>
                            <button type="button" x-on:click="open = false; $wire.set('showCsvModal', false)"
                                    class="mt-3 sm:mt-0 w-full sm:w-auto px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium text-sm">
                                Close
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- QR Code Modal --}}
    <div x-data="{ open: @entangle('showQrModal') }" x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-on:click="$wire.set('showQrModal', false)"></div>
            <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-sm sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 text-center">
                    @if ($qrLocation)
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $qrLocation->name }}</h3>
                        <p class="text-sm text-gray-500 mb-4">{{ $qrLocation->type->label() }} @if($qrLocation->building) &middot; {{ $qrLocation->building }} @endif</p>

                        <div class="flex justify-center mb-4">
                            <div class="p-3 bg-white border border-gray-200 rounded-xl">
                                {!! app(\App\Services\QrCodeService::class)->inlineSvg($qrLocation->scanUrl(), 200) !!}
                            </div>
                        </div>

                        <p class="text-xs text-gray-400 mb-4 break-all">{{ $qrLocation->scanUrl() }}</p>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="{{ route('admin.locations.print', ['ids' => [$qrLocation->id]]) }}"
                               target="_blank"
                               class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors text-center">
                                Print
                            </a>
                            <a href="{{ route('admin.locations.pdf', ['ids' => [$qrLocation->id]]) }}"
                               class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors text-center">
                                Download PDF
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-data="{ open: @entangle('deleteId') }" x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" x-on:click="$wire.set('deleteId', null)"></div>
            <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Location</h3>
                    <p class="text-sm text-gray-500">Are you sure you want to delete this location? Child locations will be unlinked.</p>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                        <button wire:click="deleteLocation"
                                type="button"
                                class="w-full sm:w-auto px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium text-sm">
                            Delete
                        </button>
                        <button type="button"
                                x-on:click="$wire.set('deleteId', null)"
                                class="mt-3 sm:mt-0 w-full sm:w-auto px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
