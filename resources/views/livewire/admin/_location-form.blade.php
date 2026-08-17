<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" wire:model="name" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10" placeholder="e.g. Main Lobby">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
        <select wire:model="type" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
            @foreach (\App\Enums\LocationType::cases() as $t)
                <option value="{{ $t->value }}">{{ $t->label() }}</option>
            @endforeach
        </select>
        @error('type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Parent (optional)</label>
        <select wire:model="parentId" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
            <option value="">None (Root)</option>
            @foreach ($allLocations as $loc)
                <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->type->label() }})</option>
            @endforeach
        </select>
        @error('parentId')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Building (optional)</label>
            <input type="text" wire:model="building" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10" placeholder="Auto from parent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Floor (optional)</label>
            <input type="text" wire:model="floor" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10" placeholder="Auto from parent">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
        <textarea wire:model="notes" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10" placeholder="Additional details..."></textarea>
        @error('notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Assign Supervisor (optional)</label>
        <select wire:model="supervisorId" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
            <option value="">No supervisor</option>
            @foreach ($supervisors as $supervisor)
                <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
            @endforeach
        </select>
        @error('supervisorId')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
