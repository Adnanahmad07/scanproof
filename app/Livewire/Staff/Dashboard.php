<?php

namespace App\Livewire\Staff;

use App\Enums\TaskStatus;
use App\Models\Task;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $workerId = auth()->id();

        $totalTasks = Task::where('assigned_to', $workerId)->count();
        $pendingTasks = Task::where('assigned_to', $workerId)->where('status', TaskStatus::Pending)->count();
        $inProgressTasks = Task::where('assigned_to', $workerId)->where('status', TaskStatus::InProgress)->count();
        $completedTasks = Task::where('assigned_to', $workerId)->where('status', TaskStatus::Completed)->count();

        $recentTasks = Task::where('assigned_to', $workerId)
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.staff.dashboard', [
            'totalTasks' => $totalTasks,
            'pendingTasks' => $pendingTasks,
            'inProgressTasks' => $inProgressTasks,
            'completedTasks' => $completedTasks,
            'recentTasks' => $recentTasks,
        ]);
    }
}
