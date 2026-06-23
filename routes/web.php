<?php

use App\Http\Controllers\Admin\QrPrintController;
use App\Http\Controllers\Admin\SupervisorInvitationController;
use App\Livewire\Admin\LocationManagement;
use App\Livewire\Supervisor\SetPassword;
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
    Route::delete('supervisors/{invitation}', [SupervisorInvitationController::class, 'destroy'])
        ->name('supervisors.cancel');

    Route::get('locations', LocationManagement::class)
        ->name('locations.index');
});

// QR Print/PDF (admin + supervisors with print permission)
Route::middleware(['auth'])->group(function () {
    Route::get('admin/locations/print', [QrPrintController::class, 'printSheet'])
        ->name('admin.locations.print');
    Route::get('admin/locations/pdf', [QrPrintController::class, 'downloadPdf'])
        ->name('admin.locations.pdf');
});

// Dashboard (handles role-based redirect)
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
});

// Staff Routes (auth + role check)
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('dashboard', function () {
        return view('pages.staff.dashboard');
    })->name('dashboard');

    Route::get('tasks', function () {
        return view('pages.staff.tasks');
    })->name('tasks');

    Route::get('scan', function () {
        return view('pages.staff.scan');
    })->name('scan');
});
