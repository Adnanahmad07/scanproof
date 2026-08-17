<div class="relative" x-data="{ open: @entangle('open') }" wire:click.away="open = false">
    {{-- Bell Button --}}
    <button wire:click="toggle"
            class="relative p-2.5 text-text-secondary hover:text-text-primary hover:bg-surface-container rounded-xl transition-all">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center bg-error text-white text-[10px] font-bold rounded-full ring-2 ring-white px-1">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-surface-high overflow-hidden z-50"
         style="display: none;">

        {{-- Header --}}
        <div class="px-4 py-3 border-b border-surface-high flex items-center justify-between">
            <h3 class="text-sm font-semibold text-text-primary">Notifications</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllRead" class="text-xs text-primary hover:underline font-medium">
                    Mark all read
                </button>
            @endif
        </div>

        {{-- Notification List --}}
        <div class="max-h-80 overflow-y-auto divide-y divide-surface-high/50">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $type = $data['type'] ?? 'unknown';
                @endphp
                <div class="px-4 py-3 hover:bg-surface-low/50 transition-colors cursor-pointer {{ $notification->read_at ? 'opacity-60' : '' }}"
                     wire:click="markAsRead('{{ $notification->id }}')">
                    <div class="flex items-start gap-3">
                        {{-- Icon --}}
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5
                            {{ match($type) {
                                'issue_reported', 'issue_assigned' => 'bg-warning/10 text-warning',
                                'task_assigned' => 'bg-info/10 text-info',
                                'task_completed' => 'bg-success/10 text-success',
                                'task_overdue' => 'bg-error/10 text-error',
                                default => 'bg-surface-low text-text-muted',
                            } }}">
                            @if(in_array($type, ['issue_reported', 'issue_assigned']))
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            @elseif($type === 'task_assigned')
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            @elseif($type === 'task_completed')
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-text-primary font-medium leading-snug">
                                @if($type === 'issue_reported')
                                    New issue reported at {{ $data['location'] ?? 'Unknown' }}
                                @elseif($type === 'issue_assigned')
                                    Issue {{ $data['tracking_code'] ?? '' }} assigned to you
                                @elseif($type === 'task_assigned')
                                    Task "{{ $data['title'] ?? '' }}" assigned to you
                                @elseif($type === 'task_completed')
                                    Task "{{ $data['title'] ?? '' }}" has been completed
                                @elseif($type === 'task_overdue')
                                    Task "{{ $data['title'] ?? '' }}" is overdue
                                @else
                                    {{ $data['message'] ?? 'Notification' }}
                                @endif
                            </p>
                            <p class="text-xs text-text-muted mt-0.5">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        {{-- Unread dot --}}
                        @unless($notification->read_at)
                            <div class="w-2 h-2 bg-primary rounded-full mt-2 shrink-0"></div>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <div class="w-12 h-12 bg-surface-low rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <p class="text-sm text-text-muted font-medium">No notifications yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
