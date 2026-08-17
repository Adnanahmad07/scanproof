<div>
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mb-4 p-4 bg-success/10 border border-success/20 rounded-xl text-success text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if($task)
        <div class="space-y-4">
            {{-- Before Photos Section --}}
            <div class="bg-white rounded-2xl border border-surface-high overflow-hidden">
                <div class="px-5 py-4 border-b border-surface-high flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-info/10 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-text-primary">Before Photos</h4>
                        <span class="text-xs text-text-muted">({{ $beforePhotos->count() }})</span>
                    </div>
                    <button wire:click="openUploadModal('before')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-info/10 text-info rounded-lg text-xs font-semibold hover:bg-info/20 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Photo
                    </button>
                </div>
                <div class="p-4">
                    @if($beforePhotos->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($beforePhotos as $taskPhoto)
                                <div class="relative group">
                                    <img src="{{ Storage::url($taskPhoto->path) }}" alt="Before photo"
                                         class="w-full h-32 object-cover rounded-xl">
                                    <div class="absolute inset-0 bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                        <a href="{{ Storage::url($taskPhoto->path) }}" target="_blank"
                                           class="p-2 bg-white/20 rounded-lg text-white hover:bg-white/30">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <button wire:click="deletePhoto({{ $taskPhoto->id }})"
                                                class="p-2 bg-error/30 rounded-lg text-white hover:bg-error/50">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-text-muted mt-1 text-center">{{ $taskPhoto->created_at->format('M d, H:i') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <p class="text-xs text-text-muted">No before photos yet. Add one before starting the task.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- After Photos Section --}}
            <div class="bg-white rounded-2xl border border-surface-high overflow-hidden">
                <div class="px-5 py-4 border-b border-surface-high flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-success/10 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-text-primary">After Photos</h4>
                        <span class="text-xs text-text-muted">({{ $afterPhotos->count() }})</span>
                    </div>
                    <button wire:click="openUploadModal('after')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-success/10 text-success rounded-lg text-xs font-semibold hover:bg-success/20 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Photo
                    </button>
                </div>
                <div class="p-4">
                    @if($afterPhotos->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($afterPhotos as $taskPhoto)
                                <div class="relative group">
                                    <img src="{{ Storage::url($taskPhoto->path) }}" alt="After photo"
                                         class="w-full h-32 object-cover rounded-xl">
                                    <div class="absolute inset-0 bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                        <a href="{{ Storage::url($taskPhoto->path) }}" target="_blank"
                                           class="p-2 bg-white/20 rounded-lg text-white hover:bg-white/30">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <button wire:click="deletePhoto({{ $taskPhoto->id }})"
                                                class="p-2 bg-error/30 rounded-lg text-white hover:bg-error/50">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-text-muted mt-1 text-center">{{ $taskPhoto->created_at->format('M d, H:i') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <p class="text-xs text-text-muted">No after photos yet. Add one when completing the task.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Upload Modal --}}
        <div x-data="{ uploaded: false }" x-show="$wire.showUploadModal" x-cloak
             x-on:upload-success.window="uploaded = true; $refs.fileInput.value = ''; setTimeout(() => uploaded = false, 100)"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="$wire.set('showUploadModal', false)"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-text-primary">Upload {{ ucfirst($photoType) }} Photo</h3>
                        <button @click="$wire.set('showUploadModal', false)" class="p-1 text-text-muted hover:text-text-primary rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="uploadPhoto" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-primary mb-1.5">Select Photo</label>
                             <div class="border-2 border-dashed border-surface-high rounded-xl p-6 text-center hover:border-primary/50 transition-colors relative">
                                <input type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp"
                                       x-ref="fileInput" wire:ignore
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                @if($photo)
                                    <div class="text-success">
                                        <svg class="w-8 h-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm font-medium">{{ $photo->getClientOriginalName() }}</p>
                                    </div>
                                @else
                                    <div class="text-text-muted">
                                        <svg class="w-8 h-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-sm">Tap to select a photo</p>
                                        <p class="text-xs mt-1">JPEG, PNG, WebP (max 5MB)</p>
                                    </div>
                                @endif
                            </div>
                            @error('photo') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="$wire.set('showUploadModal', false)"
                                    class="flex-1 px-4 py-2.5 border border-surface-high text-text-secondary rounded-xl text-sm font-semibold hover:bg-surface-low transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm">
                                Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
