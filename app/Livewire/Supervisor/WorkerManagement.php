<?php

namespace App\Livewire\Supervisor;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class WorkerManagement extends Component
{
    use WithPagination;

    public $showAddModal = false;
    public $workerName = '';
    public $workerEmail = '';
    public $workerPassword = '';
    public $workerToDelete = null;

    protected $rules = [
        'workerName' => 'required|string|max:255',
        'workerEmail' => 'required|email|max:255',
        'workerPassword' => 'required|string|min:8|max:255',
    ];

    protected $messages = [
        'workerName.required' => 'Name is required.',
        'workerName.max' => 'Name is too long.',
        'workerEmail.required' => 'Email address is required.',
        'workerEmail.email' => 'Please enter a valid email address.',
        'workerEmail.max' => 'Email address is too long.',
        'workerEmail.unique' => 'A user with this email already exists.',
        'workerPassword.required' => 'Password is required.',
        'workerPassword.min' => 'Password must be at least 8 characters.',
    ];

    public function getWorkersProperty()
    {
        return User::where('supervisor_id', auth()->id())
            ->where('organization_id', auth()->user()->organization_id)
            ->latest()
            ->paginate(10);
    }

    public function addWorker()
    {
        $this->validate();

        $email = strtolower($this->workerEmail);

        if (User::where('email', $email)
            ->where('organization_id', auth()->user()->organization_id)
            ->exists()) {
            $this->addError('workerEmail', 'A user with this email already exists.');
            return;
        }

        User::create([
            'name' => $this->workerName,
            'email' => $email,
            'password' => Hash::make($this->workerPassword),
            'role' => UserRole::Staff,
            'supervisor_id' => auth()->id(),
            'organization_id' => auth()->user()->organization_id,
        ]);

        $this->workerName = '';
        $this->workerEmail = '';
        $this->workerPassword = '';
        $this->showAddModal = false;

        session()->flash('success', 'Worker added successfully.');
    }

    public function confirmDelete($workerId)
    {
        $this->workerToDelete = $workerId;
    }

    public function deleteWorker()
    {
        if ($this->workerToDelete) {
            User::where('id', $this->workerToDelete)
                ->where('supervisor_id', auth()->id())
                ->update(['supervisor_id' => null]);

            $this->workerToDelete = null;
            session()->flash('success', 'Worker removed from your team.');
        }
    }

    public function render()
    {
        return view('livewire.supervisor.worker-management', [
            'workers' => $this->workers,
        ]);
    }
}
