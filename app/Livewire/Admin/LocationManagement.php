<?php

namespace App\Livewire\Admin;

use App\Enums\LocationType;
use App\Enums\UserRole;
use App\Models\Location;
use App\Models\User;
use App\Services\LocationImporter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class LocationManagement extends Component
{
    use WithFileUploads;

    // --- Modal state ---
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showCsvModal = false;
    public bool $showQrModal = false;
    public ?int $deleteId = null;

    // --- Form fields ---
    public string $name = '';
    public string $type = 'room';
    public ?int $parentId = null;
    public string $building = '';
    public string $floor = '';
    public string $notes = '';
    public ?int $editId = null;
    public ?int $supervisorId = null;

    // --- CSV ---
    public $csvFile = null;
    public array $importResult = [];

    // --- Print selection ---
    public array $selectedForPrint = [];

    // --- QR modal ---
    public ?Location $qrLocation = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', LocationType::values()),
            'parentId' => 'nullable|integer|exists:locations,id',
            'building' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'supervisorId' => 'nullable|integer|exists:users,id',
        ];
    }

    protected $messages = [
        'name.required' => 'Location name is required.',
        'name.max' => 'Name is too long.',
        'type.in' => 'Invalid location type.',
        'parentId.exists' => 'Selected parent location does not exist.',
    ];

    public function getLocationsProperty()
    {
        return Location::with(['children', 'supervisor'])->roots()->orderBy('name')->get();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openCsvModal(): void
    {
        $this->csvFile = null;
        $this->importResult = [];
        $this->showCsvModal = true;
    }

    public function createLocation(): void
    {
        $this->validate();

        $parent = $this->parentId ? Location::find($this->parentId) : null;
        $type = LocationType::from($this->type);

        if (!$type->allowsParent($parent?->type)) {
            $this->addError('parentId', 'The selected parent is not valid for this location type.');
            return;
        }

        $building = $this->building !== '' ? $this->building : ($parent?->building ?? null);
        $floor = $this->floor !== '' ? $this->floor : ($parent?->floor ?? null);

        Location::create([
            'name' => $this->name,
            'type' => $type,
            'parent_id' => $this->parentId,
            'building' => $building,
            'floor' => $floor,
            'notes' => $this->notes ?: null,
            'created_by' => auth()->id(),
            'supervisor_id' => $this->supervisorId,
        ]);

        $this->showCreateModal = false;
        $this->resetForm();
        session()->flash('success', 'Location created successfully.');
    }

    public function openEditModal(int $id): void
    {
        $location = Location::findOrFail($id);
        $this->editId = $location->id;
        $this->name = $location->name;
        $this->type = $location->type->value;
        $this->parentId = $location->parent_id;
        $this->building = $location->building ?? '';
        $this->floor = $location->floor ?? '';
        $this->notes = $location->notes ?? '';
        $this->supervisorId = $location->supervisor_id;
        $this->showEditModal = true;
    }

    public function updateLocation(): void
    {
        $this->validate();

        $location = Location::findOrFail($this->editId);
        $parent = $this->parentId ? Location::find($this->parentId) : null;
        $type = LocationType::from($this->type);

        if (!$type->allowsParent($parent?->type)) {
            $this->addError('parentId', 'The selected parent is not valid for this location type.');
            return;
        }

        $building = $this->building !== '' ? $this->building : ($parent?->building ?? null);
        $floor = $this->floor !== '' ? $this->floor : ($parent?->floor ?? null);

        $location->update([
            'name' => $this->name,
            'type' => $type,
            'parent_id' => $this->parentId,
            'building' => $building,
            'floor' => $floor,
            'notes' => $this->notes,
            'supervisor_id' => $this->supervisorId,
        ]);

        $this->showEditModal = false;
        $this->editId = null;
        $this->resetForm();
        session()->flash('success', 'Location updated successfully.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function deleteLocation(): void
    {
        $location = Location::findOrFail($this->deleteId);
        $location->delete();
        $this->deleteId = null;
        session()->flash('success', 'Location deleted successfully.');
    }

    public function importCsv(): void
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $contents = file_get_contents($this->csvFile->getRealPath());
        $importer = new LocationImporter();
        $this->importResult = $importer->import($contents, auth()->user());

        if ($this->importResult['created'] > 0) {
            session()->flash('success', "Imported {$this->importResult['created']} location(s).");
        }
    }

    public function togglePrintSelection(int $locationId): void
    {
        if (in_array($locationId, $this->selectedForPrint, true)) {
            $this->selectedForPrint = array_values(array_diff($this->selectedForPrint, [$locationId]));
        } else {
            $this->selectedForPrint[] = $locationId;
        }
    }

    public function selectAll(): void
    {
        $this->selectedForPrint = Location::pluck('id')->toArray();
    }

    public function clearSelection(): void
    {
        $this->selectedForPrint = [];
    }

    public function showQr(int $id): void
    {
        $this->qrLocation = Location::with('parent')->find($id);
        $this->showQrModal = true;
    }

    public function getAllLocationsCountProperty(): int
    {
        return Location::count();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->type = 'room';
        $this->parentId = null;
        $this->building = '';
        $this->floor = '';
        $this->notes = '';
        $this->supervisorId = null;
    }

    public function render()
    {
        return view('livewire.admin.location-management', [
            'locations' => $this->locations,
            'allLocations' => Location::orderBy('name')->get(),
            'allLocationsCount' => $this->allLocationsCount,
            'supervisors' => User::where('role', UserRole::Supervisor)->orderBy('name')->get(),
        ]);
    }
}
