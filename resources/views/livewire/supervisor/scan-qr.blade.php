<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-xl font-bold text-text-primary">My Locations</h1>
        <p class="text-xs text-text-secondary mt-0.5">Your assigned locations with QR codes</p>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-2xl border border-surface-high p-4">
        <input type="text" wire:model.live="search" placeholder="Search locations..."
               class="w-full px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
    </div>

    {{-- Locations List --}}
    <div class="space-y-3">
        @forelse ($locations as $loc)
            <div class="bg-white rounded-2xl border border-surface-high p-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $loc->type->icon() }}" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-text-primary">{{ $loc->name }}</p>
                            <p class="text-xs text-text-muted">{{ $loc->type->label() }} {{ $loc->building ? '· ' . $loc->building : '' }} {{ $loc->floor ? '· ' . $loc->floor : '' }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success/10 text-success">Active</span>
                </div>
                <div class="mt-3 flex items-center justify-between bg-surface-low rounded-xl p-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-text-muted">Scan URL</p>
                        <p class="text-xs text-primary font-medium truncate">{{ $loc->scanUrl() }}</p>
                    </div>
                    <div class="ml-3 shrink-0">
                        {!! $qr->inlineSvg($loc->scanUrl(), 80) !!}
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-surface-high p-8 text-center">
                <p class="text-sm text-text-muted">No locations assigned to you</p>
            </div>
        @endforelse
    </div>

</div>
