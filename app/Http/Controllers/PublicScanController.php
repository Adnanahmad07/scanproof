<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Task;
use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicScanController extends Controller
{
    public function show(string $uuid)
    {
        $location = Location::where('uuid', $uuid)
            ->with(['parent', 'children'])
            ->firstOrFail();

        $user = Auth::user();

        if ($user && $user->isStaff()) {
            return redirect()->route('staff.scan', ['location' => $location->uuid]);
        }

        $activeTasks = $location->tasks()
            ->whereNotIn('status', [TaskStatus::Completed, TaskStatus::Verified])
            ->latest()
            ->limit(10)
            ->get();

        return view('pages.public.scan', compact('location', 'activeTasks'));
    }
}