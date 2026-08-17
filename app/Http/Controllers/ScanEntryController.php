<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScanEntryController extends Controller
{
    /**
     * Worker entry point - redirect to staff scan or login.
     */
    public function worker(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('message', 'Please log in to access the scan feature.');
        }

        $user = auth()->user();

        if ($user->isStaff()) {
            return redirect()->route('staff.scan');
        }

        if ($user->isSupervisor()) {
            return redirect()->route('supervisor.scan-qr');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.locations.index');
        }

        return redirect()->route('login');
    }

    /**
     * Homeowner entry point - show tracking and QR scan options.
     */
    public function homeowner()
    {
        return view('pages.scan.homeowner-entry');
    }

    /**
     * Handle tracking code lookup.
     */
    public function track(Request $request)
    {
        $request->validate([
            'tracking_code' => 'required|string|max:20',
        ]);

        return redirect()->route('track.show', $request->tracking_code);
    }
}
