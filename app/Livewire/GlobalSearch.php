<?php

namespace App\Livewire;

use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;
use App\Models\User;
use Livewire\Component;

class GlobalSearch extends Component
{
    public $query = '';
    public $results = [];
    public $showResults = false;
    public $loading = false;

    protected $listeners = ['closeSearch' => 'closeSearch'];

    public function updatedQuery(): void
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            $this->showResults = false;
            return;
        }

        $this->loading = true;
        $this->performSearch();
        $this->loading = false;
        $this->showResults = true;
    }

    private function performSearch(): void
    {
        $q = $this->query;
        $user = auth()->user();
        $userId = $user->id;
        $role = $user->role->value;
        $orgId = $user->organization_id;

        $results = [];

        // Search Tasks — role-based filtering
        $tasks = Task::where('organization_id', $orgId)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('location', 'like', "%{$q}%");
            });

        if ($role === 'supervisor') {
            $tasks->where('supervisor_id', $userId);
        } elseif ($role === 'staff') {
            $tasks->where('assigned_to', $userId);
        }
        // Admin: no additional filter (sees all)

        foreach ($tasks->limit(5)->get() as $task) {
            $results[] = [
                'type' => 'task',
                'icon' => 'task',
                'title' => $task->title,
                'subtitle' => $task->location . ' - ' . $task->status->label(),
                'url' => $role === 'staff' ? route('staff.tasks') : route('supervisor.tasks'),
            ];
        }

        // Search Issues — role-based filtering
        $issues = Issue::where('organization_id', $orgId)
            ->where(function ($query) use ($q) {
                $query->where('description', 'like', "%{$q}%")
                      ->orWhere('tracking_code', 'like', "%{$q}%");
            });

        if ($role === 'supervisor') {
            $issues->forSupervisor($userId);
        } elseif ($role === 'staff') {
            $issues->forStaff($userId);
        }
        // Admin: no additional filter (sees all)

        foreach ($issues->limit(5)->get() as $issue) {
            $results[] = [
                'type' => 'issue',
                'icon' => 'issue',
                'title' => $issue->tracking_code,
                'subtitle' => \Illuminate\Support\Str::limit($issue->description, 50),
                'url' => $role === 'staff' ? route('staff.issues.index') : route('supervisor.issues.index'),
            ];
        }

        // Search Locations — role-based filtering
        $locations = Location::where('organization_id', $orgId)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('building', 'like', "%{$q}%")
                      ->orWhere('floor', 'like', "%{$q}%");
            });

        if ($role === 'supervisor') {
            $locations->forSupervisor($userId);
        } elseif ($role === 'staff') {
            // Staff don't manage locations — only show locations from their assigned tasks
            $locations->whereIn('id', Task::where('assigned_to', $userId)->pluck('location_id'));
        }
        // Admin: no additional filter (sees all)

        foreach ($locations->limit(5)->get() as $location) {
            $results[] = [
                'type' => 'location',
                'icon' => 'location',
                'title' => $location->name,
                'subtitle' => collect([$location->building, $location->floor])->filter()->implode(', ') ?: 'No details',
                'url' => $role === 'admin' ? route('admin.locations.index') : route('supervisor.scan-qr'),
            ];
        }

        // Search Users (admin only)
        if ($role === 'admin') {
            $users = User::where('organization_id', $orgId)
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%");
                })->limit(5)->get();

            foreach ($users as $user) {
                $results[] = [
                    'type' => 'user',
                    'icon' => 'user',
                    'title' => $user->name,
                    'subtitle' => $user->email . ' - ' . $user->role->label(),
                    'url' => route('admin.users.index'),
                ];
            }
        }

        $this->results = $results;
    }

    public function closeSearch(): void
    {
        $this->showResults = false;
        $this->query = '';
        $this->results = [];
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}
