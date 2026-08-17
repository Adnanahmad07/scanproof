<div>
    @if($task)
        <div class="space-y-6">
            {{-- Task Info --}}
            <div class="bg-white rounded-2xl border border-surface-high p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-text-primary">{{ $task->title }}</h3>
                        <p class="text-sm text-text-muted mt-1">{{ is_object($task->location) ? $task->location->name : $task->location }} &middot; {{ $task->category->label() }}</p>
                        @if($task->assignee)
                            <p class="text-sm text-text-muted mt-1">Assigned to: <span class="font-medium text-text-primary">{{ $task->assignee->name }}</span></p>
                        @endif
                        @if($task->due_date)
                            <p class="text-sm text-text-muted mt-1">Due: {{ $task->due_date->format('M d, Y') }}</p>
                        @endif
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                        @if($task->status->value === 'pending') bg-warning/10 text-warning
                        @elseif($task->status->value === 'in_progress') bg-info/10 text-info
                        @elseif($task->status->value === 'completed') bg-success/10 text-success
                        @elseif($task->status->value === 'verified') bg-success/10 text-success
                        @else bg-error/10 text-error
                        @endif">
                        {{ $task->status->label() }}
                    </span>
                </div>
                @if($task->description)
                    <p class="text-sm text-text-secondary mt-3">{{ $task->description }}</p>
                @endif
            </div>

            {{-- Before/After Comparison --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Before Photos --}}
                <div class="bg-white rounded-2xl border border-surface-high overflow-hidden">
                    <div class="px-5 py-4 border-b border-surface-high">
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
                    </div>
                    <div class="p-4">
                        @if($beforePhotos->count() > 0)
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($beforePhotos as $photo)
                                    <div class="relative">
                                        <img src="{{ Storage::url($photo->path) }}" alt="Before"
                                             class="w-full h-40 object-cover rounded-xl">
                                        <p class="text-[10px] text-text-muted mt-1 text-center">{{ $photo->created_at->format('M d, H:i') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-sm text-text-muted">No before photos uploaded</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- After Photos --}}
                <div class="bg-white rounded-2xl border border-surface-high overflow-hidden">
                    <div class="px-5 py-4 border-b border-surface-high">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-success/10 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-semibold text-text-primary">After Photos</h4>
                            <span class="text-xs text-text-muted">({{ $afterPhotos->count() }})</span>
                        </div>
                    </div>
                    <div class="p-4">
                        @if($afterPhotos->count() > 0)
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($afterPhotos as $photo)
                                    <div class="relative">
                                        <img src="{{ Storage::url($photo->path) }}" alt="After"
                                             class="w-full h-40 object-cover rounded-xl">
                                        <p class="text-[10px] text-text-muted mt-1 text-center">{{ $photo->created_at->format('M d, H:i') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-sm text-text-muted">No after photos uploaded yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-text-muted">Task not found or you don't have access.</p>
        </div>
    @endif
</div>
