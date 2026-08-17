<?php

namespace App\Livewire\Staff;

use App\Models\Issue;
use App\Models\Location;
use Livewire\Component;

class ScanScreen extends Component
{
    public $scanCode = '';
    public $foundLocation = null;
    public $foundIssues = [];
    public $showCamera = true;

    protected $rules = [
        'scanCode' => 'required|string|max:255',
    ];

    public function scanCode()
    {
        $this->validate();
        $this->foundIssues = [];

        $location = Location::where('uuid', $this->scanCode)->first();

        if (!$location) {
            $this->addError('scanCode', 'Location not found for this code.');
            $this->foundLocation = null;
            return;
        }

        $this->foundLocation = $location;

        $this->foundIssues = Issue::where('location_id', $location->id)
            ->where('assigned_to', auth()->id())
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with('location')
            ->get()
            ->toArray();
    }

    public function startIssue($issueId)
    {
        $issue = Issue::where('id', $issueId)
            ->where('assigned_to', auth()->id())
            ->where('status', 'assigned')
            ->first();

        if ($issue) {
            $issue->transitionTo('in_progress', auth()->id());
            $this->refreshIssues();
            session()->flash('success', 'Issue started!');
        }
    }

    public function completeIssue($issueId)
    {
        $issue = Issue::where('id', $issueId)
            ->where('assigned_to', auth()->id())
            ->where('status', 'in_progress')
            ->first();

        if ($issue) {
            $issue->transitionTo('resolved', auth()->id());
            $this->refreshIssues();
            session()->flash('success', 'Issue completed!');
        }
    }

    private function refreshIssues()
    {
        if (!$this->foundLocation) {
            return;
        }

        $this->foundIssues = Issue::where('location_id', $this->foundLocation->id)
            ->where('assigned_to', auth()->id())
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with('location')
            ->get()
            ->toArray();
    }

    public function resetScan()
    {
        $this->scanCode = '';
        $this->foundLocation = null;
        $this->foundIssues = [];
        $this->showCamera = false;
    }

    public function render()
    {
        return view('livewire.staff.scan-screen');
    }
}
