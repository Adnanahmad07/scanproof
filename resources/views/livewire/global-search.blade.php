<div class="relative" wire:click.away="showResults = false">
    <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" wire:model.live.debounce.300ms="query"
               placeholder="Search tasks, issues, locations..."
               class="w-full pl-10 pr-4 py-2.5 text-sm bg-surface-low border border-surface-high rounded-xl placeholder:text-text-muted focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
               autocomplete="off">
        @if($loading)
            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="animate-spin h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        @endif
    </div>

    {{-- Results Dropdown --}}
    @if($showResults && count($results) > 0)
        <div class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-surface-high overflow-hidden z-50 max-h-80 overflow-y-auto">
            @foreach($results as $result)
                <a href="{{ $result['url'] }}" wire:click="closeSearch"
                   class="flex items-center gap-3 px-4 py-3 hover:bg-surface-low/50 transition-colors border-b border-surface-high/30 last:border-0">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                        {{ match($result['type']) {
                            'task' => 'bg-info/10 text-info',
                            'issue' => 'bg-warning/10 text-warning',
                            'location' => 'bg-primary/10 text-primary',
                            'user' => 'bg-success/10 text-success',
                            default => 'bg-surface-low text-text-muted',
                        } }}">
                        @if($result['type'] === 'task')
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        @elseif($result['type'] === 'issue')
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        @elseif($result['type'] === 'location')
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        @else
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-text-primary truncate">{{ $result['title'] }}</p>
                        <p class="text-xs text-text-muted truncate">{{ $result['subtitle'] }}</p>
                    </div>
                    <span class="text-[10px] font-medium text-text-muted uppercase shrink-0">{{ $result['type'] }}</span>
                </a>
            @endforeach
        </div>
    @elseif($showResults && strlen($query) >= 2)
        <div class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-surface-high overflow-hidden z-50">
            <div class="px-4 py-6 text-center">
                <p class="text-sm text-text-muted">No results found for "{{ $query }}"</p>
            </div>
        </div>
    @endif
</div>
