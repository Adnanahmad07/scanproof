<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Notifications\IssueResolvedNotification;
use Illuminate\Http\Request;

class StaffIssueController extends Controller
{
    public function index()
    {
        $issues = Issue::forStaff(auth()->id())
            ->with(['location'])
            ->latest()
            ->get();

        return view('pages.staff.issues', compact('issues'));
    }

    public function show(Issue $issue)
    {
        abort_unless($issue->assigned_to === auth()->id(), 403);

        $issue->load(['location', 'events.actor']);

        return view('pages.staff.issue-show', ['issue' => $issue]);
    }

    public function updateStatus(Request $request, Issue $issue)
    {
        abort_unless($issue->assigned_to === auth()->id(), 403);

        $request->validate([
            'status' => 'required|in:in_progress,resolved',
        ]);

        $issue->transitionTo($request->status, auth()->id());

        if ($request->status === 'resolved') {
            $supervisor = $issue->location?->supervisor;
            if ($supervisor) {
                $supervisor->notify(new IssueResolvedNotification($issue));
            }
        }

        return back()->with('success', 'Issue status updated.');
    }
}
