<?php

namespace App\Livewire\Supervisor;

use App\Enums\LocationType;
use App\Models\Location;
use App\Services\QrCodeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.supervisor')]
class LocationQrManagement extends Component
{
    use AuthorizesRequests;

    public ?string $search = null;
    public ?string $selectedBuilding = null;
    public ?string $selectedFloor = null;

    public array $selectedForPrint = [];

    public bool $showPreviewModal = false;
    public bool $showCreateModal = false;
    public ?int $previewLocationId = null;

    public int $createParentId = 0;
    public string $newName = '';
    public ?string $newNotes = null;
    public string $newType = 'room';

    protected $queryString = ['search', 'selectedBuilding', 'selectedFloor'];

    protected $rules = [
        'newName' => 'required|string|min:1|max:255',
        'newNotes' => 'nullable|string|max:1000',
        'newType' => 'required|string|in:room,asset,checkpoint',
        'createParentId' => 'required|integer|exists:locations,id',
    ];

    protected $messages = [
        'newName.required' => 'Location name is required.',
        'newName.max' => 'Location name must not exceed 255 characters.',
        'newType.in' => 'Invalid location type.',
        'createParentId.required' => 'Please select a parent location.',
        'createParentId.exists' => 'Selected parent location does not exist.',
    ];

    public function getLocationsProperty(): Collection
    {
        return $this->scopedQuery()
            ->with(['parent', 'children', 'activeTasks'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('building', 'like', '%' . $this->search . '%')
                      ->orWhere('floor', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedBuilding, function ($query) {
                $query->where('building', $this->selectedBuilding);
            })
            ->when($this->selectedFloor, function ($query) {
                $query->where('floor', $this->selectedFloor);
            })
            ->orderBy('building')
            ->orderBy('floor')
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    public function getBuildingsProperty(): Collection
    {
        return $this->scopedQuery()
            ->whereNotNull('building')
            ->distinct()
            ->pluck('building')
            ->sort();
    }

    public function getFloorsProperty(): Collection
    {
        return $this->scopedQuery()
            ->when($this->selectedBuilding, function ($query) {
                $query->where('building', $this->selectedBuilding);
            })
            ->whereNotNull('floor')
            ->distinct()
            ->pluck('floor')
            ->sort();
    }

    protected function scopedQuery()
    {
        return Location::where('organization_id', auth()->user()->organization_id)
            ->where(function ($query) {
                $query->where('supervisor_id', auth()->id())
                      ->orWhere('created_by', auth()->id());
            });
    }

    public function showPreview(int $locationId): void
    {
        $location = $this->scopedQuery()->with('parent')->findOrFail($locationId);
        $this->previewLocationId = $location->id;
        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewLocationId = null;
    }

    public function openCreateModal(int $parentId): void
    {
        $parent = $this->scopedQuery()->findOrFail($parentId);
        $this->resetForm();
        $this->createParentId = $parentId;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function createLocation(): void
    {
        $this->validate();

        $parent = Location::findOrFail($this->createParentId);
        $parentType = $parent->type;

        $childType = LocationType::from($this->newType);

        if (!$childType->allowsParent($parentType)) {
            session()->flash('error', 'Cannot create a ' . $childType->label() . ' under a ' . $parentType->label() . '.');
            return;
        }

        $duplicate = Location::where('parent_id', $parent->id)
            ->where('type', $childType)
            ->where('name', $this->newName)
            ->exists();

        if ($duplicate) {
            $this->addError('newName', 'A location with this name already exists under this parent.');
            return;
        }

        Location::create([
            'name' => $this->newName,
            'type' => $childType,
            'parent_id' => $parent->id,
            'building' => $parent->building ?? $parent->name,
            'floor' => $parent->floor ?? ($parent->type === LocationType::Floor ? $parent->name : null),
            'notes' => $this->newNotes,
            'created_by' => auth()->id(),
            'supervisor_id' => auth()->id(),
            'organization_id' => auth()->user()->organization_id,
        ]);

        session()->flash('success', '"' . $this->newName . '" created successfully.');
        $this->closeCreateModal();
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
        $this->selectedForPrint = $this->locations->pluck('id')->toArray();
    }

    public function clearSelection(): void
    {
        $this->selectedForPrint = [];
    }

    public function getSelectedCountProperty(): int
    {
        return count($this->selectedForPrint);
    }

    public function getPrintUrl(): string
    {
        if (empty($this->selectedForPrint)) {
            return '#';
        }

        return route('supervisor.locations.print', ['ids' => $this->selectedForPrint]);
    }

    public function getPdfUrl(): string
    {
        if (empty($this->selectedForPrint)) {
            return '#';
        }

        return route('supervisor.locations.pdf', ['ids' => $this->selectedForPrint]);
    }

    protected function resetForm(): void
    {
        $this->newName = '';
        $this->newNotes = null;
        $this->newType = 'room';
        $this->createParentId = 0;
        $this->resetValidation();
    }

    public function getPreviewLocationProperty(): ?Location
    {
        return $this->previewLocationId
            ? $this->scopedQuery()->with('parent')->find($this->previewLocationId)
            : null;
    }

    public function render(QrCodeService $qr)
    {
        $previewLocation = $this->previewLocationId
            ? $this->scopedQuery()->with('parent')->find($this->previewLocationId)
            : null;

        return view('livewire.supervisor.location-qr-management', [
            'locations' => $this->locations,
            'buildings' => $this->buildings,
            'floors' => $this->floors,
            'selectedCount' => $this->selectedCount,
            'previewLocation' => $previewLocation,
            'printUrl' => $this->getPrintUrl(),
            'pdfUrl' => $this->getPdfUrl(),
            'qr' => $qr,
        ]);
    }
}
