<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SupervisorInvitationMail;
use App\Models\SupervisorInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SupervisorInvitationController extends Controller
{
    public function index()
    {
        return view('admin.supervisors.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower($request->email);

        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'A user with this email already exists.',
            ]);
        }

        $existingInvitation = SupervisorInvitation::where('email', $email)
            ->pending()
            ->exists();

        if ($existingInvitation) {
            throw ValidationException::withMessages([
                'email' => 'An invitation has already been sent to this email.',
            ]);
        }

        $invitation = SupervisorInvitation::create([
            'email' => $email,
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => auth()->id(),
            'expires_at' => now()->addHours(48),
            'status' => 'pending',
        ]);

        Mail::to($email)->send(new SupervisorInvitationMail($invitation));

        return redirect()
            ->route('admin.supervisors.index')
            ->with('success', 'Invitation sent successfully to ' . $email);
    }

    public function destroy(SupervisorInvitation $invitation)
    {
        if ($invitation->status !== 'pending') {
            throw ValidationException::withMessages([
                'invitation' => 'Only pending invitations can be cancelled.',
            ]);
        }

        $invitation->markExpired();

        return redirect()
            ->route('admin.supervisors.index')
            ->with('success', 'Invitation cancelled successfully.');
    }
}
