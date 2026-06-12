<?php

namespace App\Livewire\Supervisor;

use App\Enums\TaskCategory;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class TaskAssignment extends Component
{
    use WithPagination;

    public $showCreateModal = false;
    public $taskTitle = '';
    public $taskDescription = '';
    public $taskLocation = '';
    public $taskCategory = 'cleaning';
    public $taskPriority = 'medium';
    public $taskDueDate = '';
    public $taskAssignedTo = '';
    public $taskToDelete = null;

    protected $rules = [
        'taskTitle' => 'required|string|max:255',
        'taskDescription' => 'nullable|string|max:1000',
        'taskLocation' => 'required|string|max:255',
        'taskCategory' => 'required|in:cleaning,maintenance,inspection,other',
        'taskPriority' => 'required|in:low,medium,high,urgent',
        'taskDueDate' => 'nullable|date|after_or_equal:today',
        'taskAssignedTo' => 'required|exists:users,id',
    ];

    protected $messages = [
        'taskTitle.required' => 'Title is required.',
        'taskLocation.required' => 'Location is required.',
        'taskAssignedTo.required' => 'Please assign this task to a worker.',
        'taskAssignedTo.exists' => 'Selected worker does not exist.',
        'taskDueDate.after_or_equal' => 'Due date must be today or later.',
    ];

    public function getWorkersProperty()
    {
        return User::where('supervisor_id', auth()->id())->get();
    }

    public function getTasksProperty()
    {
        return Task::where('supervisor_id', auth()->id())
            ->latest()
            ->paginate(10);
    }

    public function createTask()
    {
        $this->validate();

        Task::create([
            'title' => $this->taskTitle,
            'description' => $this->taskDescription,
            'location' => $this->taskLocation,
            'category' => $this->taskCategory,
            'priority' => $this->taskPriority,
            'status' => TaskStatus::Pending,
            'due_date' => $this->taskDueDate ?: null,
            'supervisor_id' => auth()->id(),
            'assigned_to' => $this->taskAssignedTo,
        ]);

        $this->resetCreateForm();
        session()->flash('success', 'Task created and assigned successfully.');
    }

    public function resetCreateForm()
    {
        $this->taskTitle = '';
        $this->taskDescription = '';
        $this->taskLocation = '';
        $this->taskCategory = 'cleaning';
        $this->taskPriority = 'medium';
        $this->taskDueDate = '';
        $this->taskAssignedTo = '';
        $this->showCreateModal = false;
    }

    public function confirmDelete($taskId)
    {
        $this->taskToDelete = $taskId;
    }

    public function deleteTask()
    {
        if ($this->taskToDelete) {
            Task::where('id', $this->taskToDelete)
                ->where('supervisor_id', auth()->id())
                ->delete();

            $this->taskToDelete = null;
            session()->flash('success', 'Task deleted.');
        }
    }

    public function render()
    {
        return view('livewire.supervisor.task-assignment', [
            'tasks' => $this->tasks,
            'workers' => $this->workers,
        ]);
    }
}
