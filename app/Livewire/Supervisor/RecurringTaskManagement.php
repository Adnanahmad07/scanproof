<?php

namespace App\Livewire\Supervisor;

use App\Enums\TaskCategory;
use App\Enums\TaskPriority;
use App\Models\Location;
use App\Models\RecurringTask;
use App\Models\User;
use Livewire\Component;

class RecurringTaskManagement extends Component
{
    public $showCreateModal = false;
    public $editingId = null;
    public $taskToDelete = null;

    public $title = '';
    public $description = '';
    public $locationId = '';
    public $category = 'cleaning';
    public $priority = 'medium';
    public $frequency = 'daily';
    public $dayOfWeek = 1;
    public $dayOfMonth = 1;
    public $assignedTo = '';

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'locationId' => 'required|exists:locations,id',
        'category' => 'required|in:cleaning,maintenance,inspection,other',
        'priority' => 'required|in:low,medium,high,urgent',
        'frequency' => 'required|in:daily,weekly,monthly',
        'dayOfWeek' => 'nullable|integer|between:0,6',
        'dayOfMonth' => 'nullable|integer|between:1,31',
        'assignedTo' => 'required|exists:users,id',
    ];

    public function getWorkersProperty()
    {
        return User::where('supervisor_id', auth()->id())
            ->where('organization_id', auth()->user()->organization_id)
            ->get();
    }

    public function getLocationsProperty()
    {
        return Location::where('organization_id', auth()->user()->organization_id)
            ->orderBy('building')
            ->orderBy('floor')
            ->orderBy('name')
            ->get();
    }

    public function getRecurringTasksProperty()
    {
        return RecurringTask::where('supervisor_id', auth()->id())
            ->where('organization_id', auth()->user()->organization_id)
            ->with(['assignee', 'location'])
            ->latest()
            ->get();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->locationId = '';
        $this->category = 'cleaning';
        $this->priority = 'medium';
        $this->frequency = 'daily';
        $this->dayOfWeek = 1;
        $this->dayOfMonth = 1;
        $this->assignedTo = '';
        $this->editingId = null;
        $this->resetValidation();
    }

    public function saveTask()
    {
        $this->validate();

        $location = Location::find($this->locationId);

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'location' => $location ? $location->name : '',
            'location_id' => $this->locationId,
            'category' => $this->category,
            'priority' => $this->priority,
            'frequency' => $this->frequency,
            'day_of_week' => $this->frequency === 'weekly' ? $this->dayOfWeek : null,
            'day_of_month' => $this->frequency === 'monthly' ? $this->dayOfMonth : null,
            'assigned_to' => $this->assignedTo,
            'supervisor_id' => auth()->id(),
            'organization_id' => auth()->user()->organization_id,
        ];

        if ($this->editingId) {
            RecurringTask::where('id', $this->editingId)
                ->where('supervisor_id', auth()->id())
                ->update($data);
            session()->flash('success', 'Recurring task updated.');
        } else {
            RecurringTask::create($data);
            session()->flash('success', 'Recurring task created.');
        }

        $this->closeCreateModal();
    }

    public function editTask($id)
    {
        $task = RecurringTask::where('id', $id)
            ->where('supervisor_id', auth()->id())
            ->first();

        if (!$task) return;

        $this->editingId = $id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->locationId = $task->location_id;
        $this->category = $task->category->value;
        $this->priority = $task->priority->value;
        $this->frequency = $task->frequency;
        $this->dayOfWeek = $task->day_of_week ?? 1;
        $this->dayOfMonth = $task->day_of_month ?? 1;
        $this->assignedTo = $task->assigned_to;
        $this->showCreateModal = true;
    }

    public function confirmDelete($id)
    {
        $this->taskToDelete = $id;
    }

    public function deleteTask()
    {
        if ($this->taskToDelete) {
            RecurringTask::where('id', $this->taskToDelete)
                ->where('supervisor_id', auth()->id())
                ->update(['is_active' => false]);

            $this->taskToDelete = null;
            session()->flash('success', 'Recurring task deactivated.');
        }
    }

    public function toggleActive($id)
    {
        RecurringTask::where('id', $id)
            ->where('supervisor_id', auth()->id())
            ->update(['is_active' => !RecurringTask::find($id)->is_active]);

        session()->flash('success', 'Recurring task status updated.');
    }

    public function render()
    {
        return view('livewire.supervisor.recurring-task-management', [
            'recurringTasks' => $this->recurringTasks,
            'workers' => $this->workers,
            'locations' => $this->locations,
        ]);
    }
}
