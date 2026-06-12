<?php

namespace App\Livewire\Staff;

use App\Enums\TaskStatus;
use App\Models\Task;
use Livewire\Component;
use Livewire\WithFileUploads;

class ScanScreen extends Component
{
    use WithFileUploads;

    public $scanCode = '';
    public $foundTask = null;
    public $showCamera = false;
    public $beforePhoto = null;
    public $afterPhoto = null;

    protected $rules = [
        'scanCode' => 'required|string|max:255',
    ];

    public function scanCode()
    {
        $this->validate();

        $this->foundTask = Task::where('assigned_to', auth()->id())
            ->where('location', 'like', '%' . $this->scanCode . '%')
            ->first();

        if (!$this->foundTask) {
            $this->addError('scanCode', 'No task found for this code.');
        }
    }

    public function startTask()
    {
        if ($this->foundTask && $this->foundTask->status === TaskStatus::Pending) {
            $this->foundTask->update(['status' => TaskStatus::InProgress]);
            $this->foundTask->refresh();

            session()->flash('success', 'Task started!');
        }
    }

    public function completeTask()
    {
        if ($this->foundTask && $this->foundTask->status === TaskStatus::InProgress) {
            $this->foundTask->update(['status' => TaskStatus::Completed]);
            $this->foundTask->refresh();

            session()->flash('success', 'Task completed!');
        }
    }

    public function uploadBeforePhoto()
    {
        if ($this->beforePhoto && $this->foundTask) {
            $path = $this->beforePhoto->store('task-photos', 'public');

            $this->foundTask->photos()->create([
                'type' => 'before',
                'path' => $path,
            ]);

            $this->beforePhoto = null;
            session()->flash('success', 'Before photo uploaded.');
        }
    }

    public function uploadAfterPhoto()
    {
        if ($this->afterPhoto && $this->foundTask) {
            $path = $this->afterPhoto->store('task-photos', 'public');

            $this->foundTask->photos()->create([
                'type' => 'after',
                'path' => $path,
            ]);

            $this->afterPhoto = null;
            session()->flash('success', 'After photo uploaded.');
        }
    }

    public function resetScan()
    {
        $this->scanCode = '';
        $this->foundTask = null;
        $this->beforePhoto = null;
        $this->afterPhoto = null;
        $this->showCamera = false;
    }

    public function render()
    {
        return view('livewire.staff.scan-screen');
    }
}
