<?php

namespace App\Livewire\Supervisor;

use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.supervisor')]
class TaskQualityControl extends Component
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
            ->where('supervisor_id', auth()->id())
            ->where('organization_id', auth()->user()->organization_id)
            ->with(['assignee', 'location', 'photos'])
            ->first();
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
        return view('livewire.supervisor.task-quality-control', [
            'task' => $this->task,
            'beforePhotos' => $this->beforePhotos,
            'afterPhotos' => $this->afterPhotos,
        ]);
    }
}
