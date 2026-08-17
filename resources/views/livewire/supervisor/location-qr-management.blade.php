<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-xl font-bold text-text-primary">QR Code Management</h1>
        <p class="text-xs text-text-secondary mt-0.5">Browse, preview, and print QR codes for your locations</p>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-error/10 border border-error/20 text-error px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-surface-high p-4 md:p-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live="search" placeholder="Search locations..."
                       class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div class="flex gap-3">
                <select wire:model.live="selectedBuilding" class="px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    <option value="">All Buildings</option>
                    @foreach ($buildings as $building)
                        <option value="{{ $building }}">{{ $building }}</option>
                    @endforeach
                </select>
                <select wire:model.live="selectedFloor" class="px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    <option value="">All Floors</option>
                    @foreach ($floors as $floor)
                        <option value="{{ $floor }}">{{ $floor }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Selection Panel --}}
    @if ($selectedCount > 0)
        <div class="bg-primary/5 border border-primary/20 rounded-2xl p-4 md:p-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-primary">{{ $selectedCount }} location(s) selected</span>
                <button wire:click="clearSelection" class="text-xs text-text-muted hover:text-error transition-colors">Clear selection</button>
            </div>
            <div class="flex items-center gap-3">
                @if (auth()->user()->canPrintQr())
                    <a href="{{ $printUrl }}" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-surface-high rounded-xl text-sm font-medium text-text-primary hover:bg-surface-low transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </a>
                    <a href="{{ $pdfUrl }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download PDF
                    </a>
                @else
                    <span class="text-xs text-text-muted">Contact admin to enable QR printing</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Locations List --}}
    <div class="bg-white rounded-2xl border border-surface-high">
        @if ($locations->isEmpty())
            <div class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-text-secondary font-medium">No locations found</p>
                <p class="text-text-muted text-sm mt-1">No locations are assigned to you or match your search.</p>
            </div>
        @else
            @php $grouped = $locations->groupBy('building'); @endphp
            @foreach ($grouped as $building => $buildingLocations)
                <div class="border-b border-surface-high last:border-b-0">
                    {{-- Building Header --}}
                    <div class="px-4 md:px-6 py-3 bg-surface-low/50 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-text-primary flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-2 0h-3m-9 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            {{ $building ?? 'Unassigned' }}
                        </h3>
                        <span class="text-xs text-text-muted">{{ $buildingLocations->count() }} location(s)</span>
                    </div>

                    {{-- Location Items --}}
                    @foreach ($buildingLocations as $location)
                        <div class="px-4 md:px-6 py-4 flex items-center justify-between hover:bg-surface-low/30 transition-colors border-b border-surface-high/50 last:border-b-0">
                            <div class="flex items-center gap-4 min-w-0">
                                {{-- Checkbox --}}
                                <input type="checkbox" wire:click="togglePrintSelection({{ $location->id }})"
                                       {{ in_array($location->id, $selectedForPrint) ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary bg-white border-surface-high rounded focus:ring-primary/20">

                                {{-- Info --}}
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-text-primary truncate">{{ $location->name }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $location->type->color() }}/10 text-{{ $location->type->color() }}">
                                            {{ $location->type->label() }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        @if ($location->floor)
                                            <span class="text-xs text-text-muted">Floor: {{ $location->floor }}</span>
                                        @endif
                                        @if ($location->activeTasks->count() > 0)
                                            <span class="text-xs text-warning">{{ $location->activeTasks->count() }} active task(s)</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 ml-4">
                                <button wire:click="showPreview({{ $location->id }})"
                                        class="p-2 text-text-muted hover:text-primary hover:bg-primary/5 rounded-lg transition-colors" title="Preview QR">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </button>
                                @if ($location->type !== \App\Enums\LocationType::Building)
                                    <button wire:click="openCreateModal({{ $location->id }})"
                                            class="p-2 text-text-muted hover:text-success hover:bg-success/5 rounded-lg transition-colors" title="Add child location">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            {{-- Select All --}}
            <div class="px-4 md:px-6 py-3 bg-surface-low/50 flex items-center justify-between">
                <button wire:click="selectAll" class="text-xs text-primary hover:underline">Select all visible</button>
            </div>
        @endif
    </div>

    {{-- QR Preview Modal --}}
    @if ($showPreviewModal && $previewLocation)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closePreview"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
                <button wire:click="closePreview" class="absolute top-4 right-4 text-text-muted hover:text-text-primary">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="text-center">
                    <div class="mb-4">{!! $qr->inlineSvg($previewLocation->scanUrl(), 200) !!}</div>
                    <h3 class="text-lg font-semibold text-text-primary">{{ $previewLocation->name }}</h3>
                    <p class="text-sm text-text-muted mt-1">{{ $previewLocation->type->label() }}</p>
                    @if ($previewLocation->building || $previewLocation->floor)
                        <p class="text-xs text-text-muted mt-1">{{ collect([$previewLocation->building, $previewLocation->floor])->filter()->implode(' · ') }}</p>
                    @endif
                    <p class="text-xs text-text-muted mt-2 break-all">{{ $previewLocation->scanUrl() }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Create Location Modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeCreateModal"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <button wire:click="closeCreateModal" class="absolute top-4 right-4 text-text-muted hover:text-text-primary">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <h3 class="text-lg font-semibold text-text-primary mb-4">Add Location</h3>
                <form wire:submit.prevent="createLocation" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-text-primary mb-1">Name *</label>
                        <input type="text" wire:model="newName" class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="e.g., Room 101">
                        @error('newName') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary mb-1">Type *</label>
                        <select wire:model="newType" class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <option value="room">Room</option>
                            <option value="asset">Asset</option>
                            <option value="checkpoint">Checkpoint</option>
                        </select>
                        @error('newType') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary mb-1">Notes</label>
                        <textarea wire:model="newNotes" rows="2" class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="Optional notes"></textarea>
                        @error('newNotes') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeCreateModal" class="px-4 py-2 text-sm font-medium text-text-secondary hover:text-text-primary transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">Create Location</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
