<?php

namespace App\Livewire\Supervisor;

use App\Models\Location;
use App\Services\QrCodeService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.supervisor')]
class ScanQr extends Component
{
    public string $search = '';

    public function getLocationsProperty()
    {
        return Location::where('supervisor_id', auth()->id())
            ->configured()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('building')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();
    }

    public function render(QrCodeService $qr)
    {
        return view('livewire.supervisor.scan-qr', [
            'locations' => $this->locations,
            'qr' => $qr,
        ]);
    }
}
