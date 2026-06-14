<?php

namespace App\Livewire\Admin;

use App\Mail\SupervisorInvitationMail;
use App\Models\SupervisorInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class SupervisorManagement extends Component
{
    use WithPagination;

    public $showInviteModal = false;
    public $inviteEmail = '';
    public $supervisorToDelete = null;

    protected $rules = [
        'inviteEmail' => 'required|email|max:255',
    ];

    protected $messages = [
        'inviteEmail.required' => 'Email address is required.',
        'inviteEmail.email' => 'Please enter a valid email address.',
        'inviteEmail.max' => 'Email address is too long.',
    ];

    public function getSupervisorsProperty()
    {
        return User::where('role', 'supervisor')
            ->latest()
            ->paginate(10);
    }

    public function getInvitationsProperty()
    {
        return SupervisorInvitation::latest()->paginate(10);
    }

    public function invite()
    {
        $this->validate();

        $email = strtolower($this->inviteEmail);

        if (User::where('email', $email)->exists()) {
            $this->addError('inviteEmail', 'A user with this email already exists.');
            return;
        }

        $existingInvitation = SupervisorInvitation::where('email', $email)
            ->pending()
            ->exists();

        if ($existingInvitation) {
            $this->addError('inviteEmail', 'An invitation has already been sent to this email.');
            return;
        }

        $invitation = SupervisorInvitation::create([
            'email' => $email,
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => auth()->id(),
            'expires_at' => now()->addHours(48),
            'status' => 'pending',
        ]);

        Mail::to($email)->send(new SupervisorInvitationMail($invitation));

        $this->inviteEmail = '';
        $this->showInviteModal = false;

        session()->flash('success', 'Invitation sent successfully to ' . $email);
    }

    public function cancelInvitation($invitationId)
    {
        $invitation = SupervisorInvitation::findOrFail($invitationId);

        if ($invitation->status === 'pending') {
            $invitation->markExpired();
            session()->flash('success', 'Invitation cancelled successfully.');
        }
    }

    public function confirmDelete($supervisorId)
    {
        $this->supervisorToDelete = $supervisorId;
    }

    public function deleteSupervisor()
    {
        if ($this->supervisorToDelete) {
            User::where('id', $this->supervisorToDelete)
                ->where('role', 'supervisor')
                ->update(['role' => 'staff']);

            $this->supervisorToDelete = null;
            session()->flash('success', 'Supervisor demoted to staff successfully.');
        }
    }

    public function togglePrintPermission(int $userId): void
    {
        $user = User::where('id', $userId)->where('role', 'supervisor')->firstOrFail();
        $user->update(['can_print_qr' => !$user->can_print_qr]);

        session()->flash('success', "Print permission " . ($user->can_print_qr ? 'granted' : 'revoked') . " for {$user->name}.");
    }

    public function render()
    {
        return view('livewire.admin.supervisor-management', [
            'supervisors' => $this->supervisors,
            'invitations' => $this->invitations,
        ]);
    }
}
