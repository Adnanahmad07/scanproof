<?php

namespace App\Livewire\Supervisor;

use App\Enums\UserRole;
use App\Models\SupervisorInvitation;
use App\Models\User;
use Livewire\Component;

class SetPassword extends Component
{
    public $token;
    public $password = '';
    public $password_confirmation = '';
    public $invitation;

    protected $rules = [
        'password' => 'required|min:8|confirmed',
    ];

    protected $messages = [
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters.',
        'password.confirmed' => 'Password confirmation does not match.',
    ];

    public function mount($token)
    {
        $this->token = $token;
        $this->invitation = SupervisorInvitation::where('token', $token)
            ->valid()
            ->first();

        if (!$this->invitation) {
            abort(404, 'Invalid or expired invitation.');
        }
    }

    public function setPassword()
    {
        $this->validate();

        if (!$this->invitation || !$this->invitation->isValidForUse()) {
            session()->flash('error', 'This invitation is no longer valid.');
            return;
        }

        $user = User::create([
            'name' => $this->getNameFromEmail($this->invitation->email),
            'email' => $this->invitation->email,
            'password' => $this->password,
            'role' => UserRole::Supervisor,
            'organization_id' => $this->invitation->organization_id,
            'email_verified_at' => now(),
        ]);

        $this->invitation->markAccepted();

        auth()->login($user);

        return redirect()->to(UserRole::Supervisor->dashboardPath());
    }

    private function getNameFromEmail(string $email): string
    {
        $name = strstr($email, '@', true);
        $name = str_replace(['.', '_', '-'], ' ', $name);
        return ucfirst($name);
    }

    public function render()
    {
        return view('livewire.supervisor.set-password')->layout('layouts.auth');
    }
}
