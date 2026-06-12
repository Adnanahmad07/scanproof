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
        <p class="text-sm text-text-muted mt-0.5">Scan a facility QR code or enter the code manually</p>
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
                   placeholder="Enter location code">
            <button type="submit"
                    class="px-6 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm">
                Search
            </button>
        </form>
        @error('scanCode') <p class="text-error text-xs mt-2">{{ $message }}</p> @enderror
    </div>

    {{-- Found Task Card --}}
    @if($foundTask)
        <div class="bg-white rounded-2xl border border-surface-high overflow-hidden">
            <div class="px-6 py-4 border-b border-surface-high bg-surface-low/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-text-primary">Task Found</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                        @if($foundTask->status->value === 'pending') bg-warning/10 text-warning
                        @elseif($foundTask->status->value === 'in_progress') bg-info/10 text-info
                        @elseif($foundTask->status->value === 'completed') bg-success/10 text-success
                        @else bg-error/10 text-error
                        @endif">
                        {{ $foundTask->status->label() }}
                    </span>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <h4 class="text-base font-bold text-text-primary">{{ $foundTask->title }}</h4>
                    <p class="text-sm text-text-muted mt-1">{{ $foundTask->location }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-info/10 text-info">
                        {{ $foundTask->category->label() }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                        @if($foundTask->priority->value === 'urgent') bg-error/10 text-error
                        @elseif($foundTask->priority->value === 'high') bg-warning/10 text-warning
                        @elseif($foundTask->priority->value === 'medium') bg-info/10 text-info
                        @else bg-success/10 text-success
                        @endif">
                        {{ $foundTask->priority->label() }}
                    </span>
                    @if($foundTask->due_date)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-surface-container text-text-secondary">
                            Due: {{ $foundTask->due_date->format('M d, Y') }}
                        </span>
                    @endif
                </div>

                @if($foundTask->description)
                    <p class="text-sm text-text-secondary">{{ $foundTask->description }}</p>
                @endif

                {{-- Photos Section --}}
                <div class="border-t border-surface-high pt-4">
                    <h5 class="text-sm font-semibold text-text-primary mb-3">Photos (Optional)</h5>
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Before Photo --}}
                        <div>
                            <p class="text-xs text-text-muted mb-2">Before Photo</p>
                            @if($foundTask->photos->where('type', 'before')->count())
                                <div class="w-full h-32 bg-surface-low rounded-xl overflow-hidden">
                                    <img src="{{ asset('storage/' . $foundTask->photos->where('type', 'before')->first()->path) }}" 
                                         class="w-full h-full object-cover" alt="Before">
                                </div>
                            @else
                                <label class="block w-full h-32 bg-surface-low rounded-xl border-2 border-dashed border-surface-high hover:border-primary cursor-pointer transition-colors">
                                    <input type="file" wire:model="beforePhoto" accept="image/*" class="hidden">
                                    <div class="flex flex-col items-center justify-center h-full">
                                        <svg class="w-8 h-8 text-text-muted mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="text-xs text-text-muted">Upload</span>
                                    </div>
                                </label>
                                @if($beforePhoto)
                                    <button wire:click="uploadBeforePhoto" wire:loading.attr="disabled"
                                            class="mt-2 w-full px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary/90 transition-colors">
                                        Save Before Photo
                                    </button>
                                @endif
                            @endif
                        </div>

                        {{-- After Photo --}}
                        <div>
                            <p class="text-xs text-text-muted mb-2">After Photo</p>
                            @if($foundTask->photos->where('type', 'after')->count())
                                <div class="w-full h-32 bg-surface-low rounded-xl overflow-hidden">
                                    <img src="{{ asset('storage/' . $foundTask->photos->where('type', 'after')->first()->path) }}" 
                                         class="w-full h-full object-cover" alt="After">
                                </div>
                            @else
                                <label class="block w-full h-32 bg-surface-low rounded-xl border-2 border-dashed border-surface-high hover:border-primary cursor-pointer transition-colors">
                                    <input type="file" wire:model="afterPhoto" accept="image/*" class="hidden">
                                    <div class="flex flex-col items-center justify-center h-full">
                                        <svg class="w-8 h-8 text-text-muted mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="text-xs text-text-muted">Upload</span>
                                    </div>
                                </label>
                                @if($afterPhoto)
                                    <button wire:click="uploadAfterPhoto" wire:loading.attr="disabled"
                                            class="mt-2 w-full px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary/90 transition-colors">
                                        Save After Photo
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="border-t border-surface-high pt-4 flex gap-3">
                    @if($foundTask->status->value === 'pending')
                        <button wire:click="startTask"
                                class="flex-1 px-4 py-2.5 bg-info text-white rounded-xl text-sm font-semibold hover:bg-info/90 transition-colors shadow-sm">
                            Start Work
                        </button>
                    @elseif($foundTask->status->value === 'in_progress')
                        <button wire:click="completeTask"
                                class="flex-1 px-4 py-2.5 bg-success text-white rounded-xl text-sm font-semibold hover:bg-success/90 transition-colors shadow-sm">
                            Complete Work
                        </button>
                    @else
                        <div class="flex-1 px-4 py-2.5 bg-success/10 text-success rounded-xl text-sm font-semibold text-center">
                            Task Completed
                        </div>
                    @endif

                    <button wire:click="resetScan"
                            class="px-4 py-2.5 border border-surface-high text-text-secondary rounded-xl text-sm font-semibold hover:bg-surface-low transition-colors">
                        Scan Again
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Camera Script --}}
    @push('scripts')
        @if($showCamera)
            <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
            <script>
                document.addEventListener('livewire:initialized', () => {
                    const html5QrCode = new Html5Qrcode("qr-reader");
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
                });
            </script>
        @endif
    @endpush
</div>
