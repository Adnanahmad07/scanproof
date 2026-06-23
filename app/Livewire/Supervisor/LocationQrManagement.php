<?php

namespace App\Livewire\Supervisor;

use App\Models\Location;
use App\Models\Task;
use App\Enums\TaskStatus;
use App\Services\QrCodeService;
use Livewire\Component;
use Livewire\WithPagination;

class LocationQrManagement extends Component
{
    use WithPagination;

    public $showQrModal = false;
    public $selectedLocation = null;
    public array $selectedForPrint = [];
    public string $search = '';

    protected $queryString = ['search'];

    public function getLocationsProperty()
    {
        return Location::with(['parent', 'tasks'])
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('building', 'like', '%' . $this->search . '%')
                    ->orWhere('floor', 'like', '%' . $this->search . '%');
            })
            ->orderBy('building')
            ->orderBy('floor')
            ->orderBy('name')
            ->paginate(15);
    }

    public function showQr(int $locationId): void
    {
        $this->selectedLocation = Location::with('parent')->findOrFail($locationId);
        $this->showQrModal = true;
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

    public function printSelected(): void
    {
        $locations = Location::whereIn('id', $this->selectedForPrint)
            ->orderBy('building')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();

        return redirect()->route('admin.locations.print', ['ids' => $this->selectedForPrint]);
    }

    public function getActiveTasksCountProperty(): int
    {
        return Task::whereNotIn('status', [TaskStatus::Completed, TaskStatus::Verified])
            ->where('supervisor_id', auth()->id())
            ->count();
    }

    public function render()
    {
        return view('livewire.supervisor.location-qr-management', [
            'locations' => $this->locations,
            'qr' => app(QrCodeService::class),
        ]);
    }
}
</content>