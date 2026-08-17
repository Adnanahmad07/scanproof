<div>
    {{-- Success Flash Message --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mb-6 p-4 bg-success/10 border border-success/20 rounded-xl text-success text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-lg font-bold text-text-primary">Scan QR Code</h2>
        <p class="text-sm text-text-muted mt-0.5">Scan a location QR code to see your assigned issues</p>
    </div>

    {{-- Camera Scanner --}}
    <div class="bg-white rounded-2xl border border-surface-high p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-text-primary">Camera Scanner</h3>
            <button wire:click="$set('showCamera', {{ $showCamera ? 'false' : 'true' }})"
                    class="px-4 py-2 {{ $showCamera ? 'bg-error text-white' : 'bg-success text-white' }} rounded-xl text-sm font-semibold hover:opacity-90 transition-colors">
                {{ $showCamera ? 'Stop Camera' : 'Start Camera' }}
            </button>
        </div>

        @if($showCamera)
            <div id="qr-reader" class="w-full rounded-xl overflow-hidden mb-4" style="min-height: 300px;"></div>
            <p class="text-xs text-text-muted text-center">Point your camera at a QR code</p>
        @else
            <div class="w-full h-48 bg-surface-low rounded-xl flex items-center justify-center">
                <div class="text-center">
                    <svg class="w-12 h-12 text-text-muted mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <p class="text-sm text-text-muted">Camera is off</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Manual Code Entry --}}
    <div class="bg-white rounded-2xl border border-surface-high p-6 mb-6">
        <h3 class="text-sm font-semibold text-text-primary mb-4">Or Enter Code Manually</h3>
        <form wire:submit="scanCode" class="flex gap-3">
            <input type="text" wire:model="scanCode"
                   class="flex-1 px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-text-primary placeholder:text-text-muted focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all text-sm"
                   placeholder="Enter location UUID">
            <button type="submit"
                    class="px-6 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm">
                Search
            </button>
        </form>
        @error('scanCode') <p class="text-error text-xs mt-2">{{ $message }}</p> @enderror
    </div>

    {{-- Found Location --}}
    @if($foundLocation)
        <div class="bg-white rounded-2xl border border-surface-high overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-surface-high bg-primary/5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $foundLocation->type->icon() }}" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-text-primary">{{ $foundLocation->name }}</h3>
                        <p class="text-xs text-text-muted">{{ $foundLocation->type->label() }} @if($foundLocation->building) &middot; {{ $foundLocation->building }} @endif</p>
                    </div>
                </div>
            </div>

            {{-- Issues at this location --}}
            <div class="p-6">
                <h4 class="text-sm font-semibold text-text-primary mb-4">Your Issues at This Location</h4>

                @if(empty($foundIssues))
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-text-muted mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-text-secondary font-medium">No issues assigned to you here</p>
                        <p class="text-xs text-text-muted mt-1">All clear! No pending issues at this location.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($foundIssues as $issue)
                            <div class="border border-surface-high rounded-xl p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-mono text-text-muted">{{ $issue['tracking_code'] }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ match($issue['status']) {
                                                    'assigned' => 'bg-info/10 text-info',
                                                    'in_progress' => 'bg-primary/10 text-primary',
                                                    default => 'bg-surface-low text-text-muted',
                                                } }}">
                                                {{ ucfirst(str_replace('_', ' ', $issue['status'])) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-text-primary mt-1 line-clamp-2">{{ $issue['description'] }}</p>
                                        <p class="text-xs text-text-muted mt-2">{{ \Carbon\Carbon::parse($issue['created_at'])->diffForHumans() }}</p>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($issue['status'] === 'assigned')
                                            <button wire:click="startIssue({{ $issue['id'] }})"
                                                    class="px-3 py-1.5 text-xs font-medium text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors">
                                                Start
                                            </button>
                                        @elseif($issue['status'] === 'in_progress')
                                            <button wire:click="completeIssue({{ $issue['id'] }})"
                                                    class="px-3 py-1.5 text-xs font-medium text-white bg-success rounded-lg hover:bg-success/90 transition-colors">
                                                Complete
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Scan Again Button --}}
        <button wire:click="resetScan"
                class="w-full px-4 py-2.5 border border-surface-high text-text-secondary rounded-xl text-sm font-semibold hover:bg-surface-low transition-colors">
            Scan Another Location
        </button>
    @endif

    {{-- Camera Script --}}
    @push('scripts')
        @if($showCamera)
            <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
            <script>
                let html5QrCode = null;

                function startCamera() {
                    if (html5QrCode) {
                        html5QrCode.stop().catch(() => {});
                    }

                    html5QrCode = new Html5Qrcode("qr-reader");
                    html5QrCode.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => {
                            @this.set('scanCode', decodedText);
                            @this.call('scanCode');
                            html5QrCode.stop();
                        },
                        (errorMessage) => {}
                    ).catch((err) => {
                        console.log("Camera error:", err);
                    });
                }

                // Start camera when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', startCamera);
                } else {
                    startCamera();
                }
            </script>
        @endif
    @endpush
</div>
