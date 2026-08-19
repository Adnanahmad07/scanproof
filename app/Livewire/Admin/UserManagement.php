<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class UserManagement extends Component
{
    use WithPagination;

    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingUserId = null;

    public $createName = '';
    public $createEmail = '';
    public $createPassword = '';
    public $createPassword_confirmation = '';
    public ?int $createSupervisorId = null;

    public $editName = '';
    public $editEmail = '';
    public $editRole = 'staff';
    public $editIsActive = true;

    protected $rules = [
        'createName' => 'required|string|max:255',
        'createEmail' => 'required|email|max:255|unique:users,email',
        'createPassword' => 'required|string|min:8|confirmed',
        'createSupervisorId' => 'nullable|integer|exists:users,id',
        'editName' => 'required|string|max:255',
        'editEmail' => 'required|email|max:255',
        'editRole' => 'required|in:admin,supervisor,staff,client',
        'editIsActive' => 'boolean',
    ];

    protected $messages = [
        'createName.required' => 'Name is required.',
        'createEmail.required' => 'Email is required.',
        'createEmail.email' => 'Please enter a valid email address.',
        'createEmail.unique' => 'A user with this email already exists.',
        'createPassword.required' => 'Password is required.',
        'createPassword.min' => 'Password must be at least 8 characters.',
        'createPassword.confirmed' => 'Password confirmation does not match.',
        'editName.required' => 'Name is required.',
        'editEmail.required' => 'Email is required.',
        'editEmail.email' => 'Please enter a valid email address.',
        'editRole.required' => 'Role is required.',
        'editRole.in' => 'Invalid role selected.',
    ];

    public function getUsersProperty()
    {
        return User::where('organization_id', auth()->user()->organization_id)
            ->latest()
            ->paginate(15);
    }

    public function openCreateModal()
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function resetCreateForm()
    {
        $this->createName = '';
        $this->createEmail = '';
        $this->createPassword = '';
        $this->createPassword_confirmation = '';
        $this->createSupervisorId = null;
        $this->resetValidation();
    }

    public function createUser()
    {
        $this->validate();

        User::create([
            'name' => $this->createName,
            'email' => strtolower($this->createEmail),
            'password' => Hash::make($this->createPassword),
            'role' => UserRole::Staff,
            'supervisor_id' => $this->createSupervisorId,
            'organization_id' => auth()->user()->organization_id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->closeCreateModal();
        session()->flash('success', 'Worker added successfully.');
    }

    public function openEditModal(int $userId)
    {
        $user = User::findOrFail($userId);

        $this->editingUserId = $userId;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editRole = $user->role->value;
        $this->editIsActive = $user->is_active;
        $this->resetValidation();

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingUserId = null;
        $this->resetValidation();
    }

    public function updateUser()
    {
        $this->validate();

        $user = User::findOrFail($this->editingUserId);

        $existingUser = User::where('email', strtolower($this->editEmail))
            ->where('organization_id', auth()->user()->organization_id)
            ->where('id', '!=', $this->editingUserId)
            ->first();

        if ($existingUser) {
            $this->addError('editEmail', 'A user with this email already exists.');
            return;
        }

        $user->update([
            'name' => $this->editName,
            'email' => strtolower($this->editEmail),
            'role' => $this->editRole,
            'is_active' => $this->editIsActive,
        ]);

        $this->closeEditModal();
        session()->flash('success', 'User updated successfully.');
    }

    public function toggleActive(int $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot deactivate your own account.');
            return;
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "User {$status} successfully.");
    }

    public function promoteToSupervisor(int $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot change your own role.');
            return;
        }

        if ($user->role === UserRole::Supervisor) {
            session()->flash('error', 'User is already a supervisor.');
            return;
        }

        $user->update(['role' => UserRole::Supervisor]);

        session()->flash('success', "{$user->name} has been promoted to Supervisor.");
    }

    public function demoteToStaff(int $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot change your own role.');
            return;
        }

        if ($user->role !== UserRole::Supervisor) {
            session()->flash('error', 'User is not a supervisor.');
            return;
        }

        $user->update(['role' => UserRole::Staff]);

        session()->flash('success', "{$user->name} has been demoted to Staff.");
    }

    public function render()
    {
        return view('livewire.admin.user-management', [
            'users' => $this->users,
            'supervisors' => User::where('role', UserRole::Supervisor)
                ->where('organization_id', auth()->user()->organization_id)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
