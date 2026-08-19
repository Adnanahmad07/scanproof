<?php

use App\Http\Controllers\Admin\QrPrintController;
use App\Http\Controllers\Admin\SupervisorInvitationController;
use App\Http\Controllers\IssueReportController;
use App\Http\Controllers\IssueTrackController;
use App\Http\Controllers\PublicScanController;
use App\Http\Controllers\ScanEntryController;
use App\Http\Controllers\StaffIssueController;
use App\Http\Controllers\SupervisorIssueController;
use App\Livewire\Admin\LocationManagement;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Supervisor\LocationQrManagement;
use App\Livewire\Supervisor\SetPassword;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.landing');
})->name('home');

Route::get('/pricing', function () {
    return view('pages.pricing');
})->name('pricing');

// Email verification notice (needed alongside Fortify)
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice')->middleware('auth');

// Logout (manual since we need redirect to /)
Route::post('/logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->name('logout')->middleware('auth');

// Supervisor Set Password (Public - uses Livewire component)
Route::get('/supervisor/set-password/{token}', SetPassword::class)
    ->name('supervisor.set-password')
    ->middleware(\App\Http\Middleware\ValidateInvitationToken::class);

// Admin Routes (auth + role check)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('supervisors', [SupervisorInvitationController::class, 'index'])
        ->name('supervisors.index');
    Route::post('supervisors/invite', [SupervisorInvitationController::class, 'store'])
        ->name('supervisors.invite');
    Route::delete('supervisors/{invitation}', function (\App\Models\SupervisorInvitation $invitation) {
        if ($invitation->organization_id !== auth()->user()->organization_id) {
            abort(403, 'Unauthorized.');
        }

        if ($invitation->status !== 'pending') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'invitation' => 'Only pending invitations can be cancelled.',
            ]);
        }

        $invitation->markExpired();

        return redirect()
            ->route('admin.supervisors.index')
            ->with('success', 'Invitation cancelled successfully.');
    })->name('supervisors.cancel');

    Route::get('users', UserManagement::class)
        ->name('users.index');

    Route::post('users', function () {
        $data = request()->validate([
            'createName' => 'required|string|max:255',
            'createEmail' => 'required|email|max:255|unique:users,email',
            'createPassword' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $data['createName'],
            'email' => strtolower($data['createEmail']),
            'password' => \Illuminate\Support\Facades\Hash::make($data['createPassword']),
            'role' => \App\Enums\UserRole::Staff,
            'organization_id' => auth()->user()->organization_id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return back()->with('success', 'User created successfully.');
    })->name('users.store');

    Route::post('users/{user}/update', function (User $user) {
        if ($user->organization_id !== auth()->user()->organization_id) {
            abort(403, 'Unauthorized.');
        }

        $data = request()->validate([
            'editName' => 'required|string|max:255',
            'editEmail' => 'required|email|max:255',
            'editRole' => 'required|in:admin,supervisor,staff,client',
            'editIsActive' => 'boolean',
        ]);

        if ($user->id === auth()->id() && $data['editRole'] !== $user->role->value) {
            return back()->withErrors(['editRole' => 'You cannot change your own role.']);
        }

        $existing = User::where('email', strtolower($data['editEmail']))
            ->where('organization_id', auth()->user()->organization_id)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['editEmail' => 'A user with this email already exists.']);
        }

        $user->update([
            'name' => $data['editName'],
            'email' => strtolower($data['editEmail']),
            'role' => $data['editRole'],
            'is_active' => $data['editIsActive'] ?? $user->is_active,
        ]);

        return back()->with('success', 'User updated successfully.');
    })->name('users.update');

    Route::post('users/{user}/toggle-active', function (User $user) {
        if ($user->organization_id !== auth()->user()->organization_id) {
            abort(403, 'Unauthorized.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully.");
    })->name('users.toggle-active');

    Route::get('locations', LocationManagement::class)
        ->name('locations.index');

    Route::get('issues', [\App\Http\Controllers\Admin\AdminIssueController::class, 'index'])
        ->name('issues.index');

    Route::get('reports', [\App\Http\Controllers\ReportController::class, 'index'])
        ->name('reports.index');
    Route::get('reports/pdf', [\App\Http\Controllers\ReportController::class, 'pdf'])
        ->name('reports.pdf');
});

// QR Print/PDF (admin + supervisors with print permission)
Route::middleware(['auth'])->group(function () {
    Route::get('admin/locations/print', [QrPrintController::class, 'printSheet'])
        ->name('admin.locations.print');
    Route::get('admin/locations/pdf', [QrPrintController::class, 'downloadPdf'])
        ->name('admin.locations.pdf');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if (!$user) {
        return redirect()->route('login');
    }

    if (!$user->role) {
        abort(403, 'No role assigned. Please contact an administrator.');
    }

    return redirect()->to($user->role->dashboardPath());
})->name('dashboard')->middleware('auth');

Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'role:admin']);

Route::get('/admin/dashboard', function () {
    return view('pages.admin.dashboard');
})->name('admin.dashboard')->middleware(['auth', 'role:admin']);

// Supervisor Routes (auth + role check)
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('dashboard', function () {
        return view('pages.supervisor.dashboard');
    })->name('dashboard');

    Route::get('workers', function () {
        return view('pages.supervisor.workers');
    })->name('workers');

    Route::get('tasks', function () {
        return view('pages.supervisor.tasks');
    })->name('tasks');

    Route::get('recurring-tasks', function () {
        return view('pages.supervisor.recurring-tasks');
    })->name('recurring-tasks');

    Route::get('tasks/{taskId}/quality', \App\Livewire\Supervisor\TaskQualityControl::class)
        ->name('task-quality');

    Route::post('tasks', function () {
        $data = request()->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'category' => 'required|in:cleaning,maintenance,inspection,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        \App\Models\Task::create(array_merge($data, [
            'supervisor_id' => auth()->id(),
            'status' => \App\Enums\TaskStatus::Pending,
            'organization_id' => auth()->user()->organization_id,
        ]));

        return back()->with('success', 'Task created successfully.');
    })->name('tasks.store');

    // Scan QR — view locations and QR codes
    Route::get('scan-qr', \App\Livewire\Supervisor\ScanQr::class)->name('scan-qr');
});

// Staff Routes (auth + role check)
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('dashboard', function () {
        return view('pages.staff.dashboard');
    })->name('dashboard');

    Route::get('tasks', function () {
        return view('pages.staff.tasks');
    })->name('tasks');

    Route::get('tasks/{taskId}', \App\Livewire\Staff\TaskDetail::class)
        ->name('tasks.show');

    Route::get('scan', function () {
        return view('pages.staff.scan');
    })->name('scan');

    // Staff Issue Routes
    Route::get('issues', [StaffIssueController::class, 'index'])->name('issues.index');
    Route::get('issues/{issue}', [StaffIssueController::class, 'show'])->name('issues.show');
    Route::post('issues/{issue}/status', [StaffIssueController::class, 'updateStatus'])->name('issues.status');
});

// Client Routes (auth + role check)
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('dashboard', function () {
        return view('pages.client.dashboard');
    })->name('dashboard');
});

// ── Scan to Solve Entry Points ──
Route::get('/scan/worker', [ScanEntryController::class, 'worker'])->name('scan.worker');
Route::get('/scan/report', [ScanEntryController::class, 'homeowner'])->name('scan.homeowner');
Route::post('/scan/track', [ScanEntryController::class, 'track'])->name('scan.track.lookup');

// ── Public Scan Routes (no auth required) ──
Route::get('/r/{uuid}', [PublicScanController::class, 'show'])->name('scan.show');
Route::get('/r/{uuid}/report', [IssueReportController::class, 'show'])->name('scan.report');
Route::post('/r/{uuid}/report', [IssueReportController::class, 'submit'])->name('scan.report.submit');
Route::get('/track/{tracking_code}', [IssueTrackController::class, 'show'])->name('track.show');

// ── Supervisor Issue Routes ──
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('issues', [SupervisorIssueController::class, 'index'])->name('issues.index');
    Route::post('issues/{issue}/assign', [SupervisorIssueController::class, 'assign'])->name('issues.assign');
    Route::post('issues/{issue}/convert-to-task', [SupervisorIssueController::class, 'convertToTask'])->name('issues.convert-to-task');
    Route::post('issues/{issue}/reject', [SupervisorIssueController::class, 'reject'])->name('issues.reject');
    Route::post('issues/{issue}/status', [SupervisorIssueController::class, 'updateStatus'])->name('issues.status');
});
