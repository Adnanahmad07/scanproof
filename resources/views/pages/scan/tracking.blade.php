<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track {{ $issue->tracking_code }} - ScanProof</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpeg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body class="bg-surface-low min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        @if (session('success'))
            <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded-xl text-sm mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-surface-high p-6">
            {{-- Header --}}
            <div class="text-center mb-6">
                <p class="text-xs text-text-muted uppercase tracking-wide">Tracking Code</p>
                <h1 class="text-2xl font-bold text-text-primary font-mono">{{ $issue->tracking_code }}</h1>
            </div>

            {{-- Location --}}
            <div class="flex items-center gap-3 p-3 bg-surface-low rounded-xl mb-6">
                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $issue->location->type->icon() }}" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-primary">{{ $issue->location->name }}</p>
                    <p class="text-xs text-text-muted">{{ $issue->location->type->label() }}</p>
                </div>
            </div>

            {{-- Status Timeline --}}
            <div class="space-y-3">
                @php
                    $statuses = ['reported', 'assigned', 'in_progress', 'resolved'];
                    $labels = ['Reported', 'Assigned', 'In Progress', 'Resolved'];
                    $currentIdx = array_search($issue->status, $statuses);
                    $isRejected = $issue->status === 'rejected';
                @endphp

                @foreach ($statuses as $idx => $status)
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full {{ $isRejected ? 'bg-error' : ($idx <= $currentIdx ? 'bg-success' : 'bg-surface-high') }}"></div>
                        <div class="flex-1">
                            <p class="text-sm {{ $isRejected || $idx <= $currentIdx ? 'text-text-primary font-medium' : 'text-text-muted' }}">{{ $labels[$idx] }}</p>
                            @if ($isRejected)
                                @if ($event = $issue->events->where('to_status', 'rejected')->first())
                                    <p class="text-xs text-error">{{ $event->note ?? 'Rejected' }}</p>
                                    <p class="text-xs text-text-muted">{{ $event->created_at->format('M d, g:i A') }}</p>
                                @endif
                            @elseif ($idx <= $currentIdx && $event = $issue->events->where('to_status', $status)->first())
                                <p class="text-xs text-text-muted">{{ $event->created_at->format('M d, g:i A') }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if ($isRejected)
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-error"></div>
                        <div class="flex-1">
                            <p class="text-sm text-text-primary font-medium text-error">Rejected</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Description --}}
            <div class="mt-6 p-3 bg-surface-low rounded-xl">
                <p class="text-xs text-text-muted uppercase tracking-wide mb-1">Description</p>
                <p class="text-sm text-text-primary">{{ $issue->description }}</p>
            </div>
        </div>

        <p class="text-center text-xs text-text-muted mt-4">ScanProof &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
