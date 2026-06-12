<?php

namespace App\Livewire\Staff;

use App\Enums\TaskStatus;
use App\Models\Task;
use Livewire\Component;
use Livewire\WithPagination;

class TaskList extends Component
{
    use WithPagination;

    public $statusFilter = '';

    public function getTasksProperty()
    {
        $query = Task::where('assigned_to', auth()->id());

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query->latest()->paginate(10);
    }

    public function startTask($taskId)
    {
        Task::where('id', $taskId)
            ->where('assigned_to', auth()->id())
            ->where('status', TaskStatus::Pending)
            ->update(['status' => TaskStatus::InProgress]);

        session()->flash('success', 'Task started.');
    }

    public function completeTask($taskId)
    {
        Task::where('id', $taskId)
            ->where('assigned_to', auth()->id())
            ->where('status', TaskStatus::InProgress)
            ->update(['status' => TaskStatus::Completed]);

        session()->flash('success', 'Task completed!');
    }

    public function render()
    {
        return view('livewire.staff.task-list', [
            'tasks' => $this->tasks,
        ]);
    }
}
