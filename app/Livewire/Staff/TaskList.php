<?php

namespace App\Livewire\Staff;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Enums\UserRole;
use App\Notifications\TaskCompletedNotification;
use Livewire\Component;
use Livewire\WithPagination;

class TaskList extends Component
{
    use WithPagination;

    public $statusFilter = '';

    public function getTasksProperty()
    {
        $query = Task::where('assigned_to', auth()->id())
            ->where('organization_id', auth()->user()->organization_id);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query->latest()->paginate(10);
    }

    public function startTask($taskId)
    {
        $task = Task::where('id', $taskId)
            ->where('assigned_to', auth()->id())
            ->where('status', TaskStatus::Pending)
            ->first();

        if ($task) {
            $hasBeforePhoto = $task->photos()->where('type', 'before')->exists();
            if (!$hasBeforePhoto) {
                session()->flash('error', 'Please upload a before photo first. Open the task to upload.');
                return;
            }
            $task->update(['status' => TaskStatus::InProgress]);
            session()->flash('success', 'Task started.');
        }
    }

    public function completeTask($taskId)
    {
        $task = Task::where('id', $taskId)
            ->where('assigned_to', auth()->id())
            ->where('status', TaskStatus::InProgress)
            ->first();

        if ($task) {
            $hasAfterPhoto = $task->photos()->where('type', 'after')->exists();
            if (!$hasAfterPhoto) {
                session()->flash('error', 'Please upload an after photo first. Open the task to upload.');
                return;
            }
            $task->update(['status' => TaskStatus::Completed]);

            if ($task->supervisor_id) {
                $supervisor = User::find($task->supervisor_id);
                if ($supervisor) {
                    $supervisor->notify(new TaskCompletedNotification($task));
                }
            }

            session()->flash('success', 'Task completed!');
        }
    }

    public function render()
    {
        return view('livewire.staff.task-list', [
            'tasks' => $this->tasks,
        ]);
    }
}
