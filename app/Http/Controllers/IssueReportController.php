<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Location;
use App\Models\User;
use App\Notifications\IssueReportedNotification;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class IssueReportController extends Controller
{
    public function show(string $uuid)
    {
        $location = Location::where('uuid', $uuid)->firstOrFail();

        return view('pages.scan.report', ['location' => $location]);
    }

    public function submit(Request $request, string $uuid)
    {
        $location = Location::where('uuid', $uuid)->firstOrFail();

        // Rate limiting: max 5 reports per IP per minute
        $key = 'issue-report:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'description' => "Too many reports. Please try again in {$seconds} seconds.",
            ])->withInput();
        }

        // Honeypot check: if website field is filled, silently reject (looks like success)
        if ($request->filled('website')) {
            return redirect()->route('track.show', 'SP-FAKE00')
                ->with('success', 'Issue reported! Your tracking code: SP-FAKE00');
        }

        $validated = $request->validate([
            'description' => 'required|string|min:10|max:2000',
            'photo' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
        ]);

        RateLimiter::hit($key, 60);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('issues', 'public');
        }

        $issue = Issue::create([
            'location_id' => $location->id,
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'reported_by' => auth()->id(),
            'organization_id' => $location->organization_id,
        ]);

        $this->notifySupervisor($location, $issue);

        return redirect()->route('track.show', $issue->tracking_code)
            ->with('success', 'Issue reported! Your tracking code: ' . $issue->tracking_code);
    }

    private function notifySupervisor(Location $location, Issue $issue): void
    {
        try {
            $supervisor = $this->findSupervisor($location);

            if ($supervisor) {
                $supervisor->notify(new IssueReportedNotification($issue));
            }
        } catch (\Throwable) {
        }
    }

    private function findSupervisor(Location $location): ?User
    {
        // 1. Check this location
        if ($location->supervisor_id) {
            return User::where('id', $location->supervisor_id)
                ->where('organization_id', $location->organization_id)
                ->first();
        }

        // 2. Walk up the hierarchy
        $current = $location->parent;
        while ($current) {
            if ($current->supervisor_id) {
                return User::where('id', $current->supervisor_id)
                    ->where('organization_id', $location->organization_id)
                    ->first();
            }
            $current = $current->parent;
        }

        // 3. Fallback: notify the org admin
        return User::where('role', UserRole::Admin)
            ->where('organization_id', $location->organization_id)
            ->first();
    }
}
