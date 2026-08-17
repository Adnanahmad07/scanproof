<?php

namespace App\Livewire\Staff;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskCompletedNotification;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.staff')]
class TaskDetail extends Component
{
    public $taskId;
    public $task;

    public function mount(int $taskId): void
    {
        $this->taskId = $taskId;
        $this->loadTask();
    }

    private function loadTask(): void
    {
        $this->task = Task::where('id', $this->taskId)
            ->where('assigned_to', auth()->id())
            ->with(['photos', 'location', 'supervisor'])
            ->first();
    }

    public function startTask(): void
    {
        if ($this->task && $this->task->status === TaskStatus::Pending) {
            $this->task->update(['status' => TaskStatus::InProgress]);
            $this->loadTask();
            session()->flash('success', 'Task started. Please upload a before photo.');
        }
    }

    public function completeTask(): void
    {
        if ($this->task && $this->task->status === TaskStatus::InProgress) {
            $this->task->update(['status' => TaskStatus::Completed]);

            if ($this->task->supervisor_id) {
                $supervisor = User::find($this->task->supervisor_id);
                if ($supervisor) {
                    $supervisor->notify(new TaskCompletedNotification($this->task));
                }
            }

            $this->loadTask();
            session()->flash('success', 'Task completed!');
        }
    }

    public function getBeforePhotosProperty()
    {
        return $this->task ? $this->task->photos()->where('type', 'before')->latest()->get() : collect();
    }

    public function getAfterPhotosProperty()
    {
        return $this->task ? $this->task->photos()->where('type', 'after')->latest()->get() : collect();
    }

    public function render()
    {
        return view('livewire.staff.task-detail', [
            'task' => $this->task,
            'beforePhotos' => $this->beforePhotos,
            'afterPhotos' => $this->afterPhotos,
        ]);
    }
}
