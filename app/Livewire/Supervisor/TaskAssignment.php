<?php

namespace App\Livewire\Supervisor;

use App\Enums\TaskCategory;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Location;
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
    public $taskLocationId = '';
    public $taskCategory = 'cleaning';
    public $taskPriority = 'medium';
    public $taskDueDate = '';
    public $taskAssignedTo = '';
    public $taskToDelete = null;

    protected $rules = [
        'taskTitle' => 'required|string|max:255',
        'taskDescription' => 'nullable|string|max:1000',
        'taskLocationId' => 'required|exists:locations,id',
        'taskCategory' => 'required|in:cleaning,maintenance,inspection,other',
        'taskPriority' => 'required|in:low,medium,high,urgent',
        'taskDueDate' => 'nullable|date|after_or_equal:today',
        'taskAssignedTo' => 'required|exists:users,id',
    ];

    protected $messages = [
        'taskTitle.required' => 'Title is required.',
        'taskLocationId.required' => 'Location is required.',
        'taskLocationId.exists' => 'Selected location does not exist.',
        'taskAssignedTo.required' => 'Please assign this task to a worker.',
        'taskAssignedTo.exists' => 'Selected worker does not exist.',
        'taskDueDate.after_or_equal' => 'Due date must be today or later.',
    ];

    public function getWorkersProperty()
    {
        return User::where('supervisor_id', auth()->id())->get();
    }

    public function getLocationsProperty()
    {
        return Location::orderBy('building')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();
    }

    public function getTasksProperty()
    {
        return Task::where('supervisor_id', auth()->id())
            ->with('location')
            ->latest()
            ->paginate(10);
    }

    public function createTask()
    {
        $this->validate();

        $location = Location::find($this->taskLocationId);

        Task::create([
            'title' => $this->taskTitle,
            'description' => $this->taskDescription,
            'location' => $location ? $location->name : '',
            'location_id' => $this->taskLocationId,
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
        $this->taskLocationId = '';
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
            'locations' => $this->locations,
        ]);
    }
}
