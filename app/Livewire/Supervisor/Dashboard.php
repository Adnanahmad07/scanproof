<?php

namespace App\Livewire\Supervisor;

use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.supervisor')]
class Dashboard extends Component
{
    public function getStatsProperty(): array
    {
        $userId = auth()->id();

        $totalTasks = Task::where('supervisor_id', $userId)->count();
        $completedTasks = Task::where('supervisor_id', $userId)
            ->where('status', 'completed')
            ->count();
        $activeLocations = Location::where('supervisor_id', $userId)
            ->configured()
            ->count();
        $openIssues = Issue::forSupervisor($userId)
            ->whereIn('status', ['reported', 'assigned', 'in_progress'])
            ->count();

        return [
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'activeLocations' => $activeLocations,
            'openIssues' => $openIssues,
        ];
    }

    public function getRecentTasksProperty()
    {
        return Task::where('supervisor_id', auth()->id())
            ->with('assignee')
            ->latest()
            ->take(5)
            ->get();
    }

    public function getRecentIssuesProperty()
    {
        return Issue::forSupervisor(auth()->id())
            ->with('location')
            ->latest()
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.supervisor.dashboard', [
            'stats' => $this->stats,
            'recentTasks' => $this->recentTasks,
            'recentIssues' => $this->recentIssues,
        ]);
    }
}
