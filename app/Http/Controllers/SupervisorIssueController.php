<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;
use App\Models\User;
use App\Notifications\IssueAssignedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupervisorIssueController extends Controller
{
    public function index()
    {
        $issues = Issue::forSupervisor(auth()->id())
            ->with(['location', 'assignee', 'task'])
            ->latest()
            ->get();

        $stats = [
            'reported' => $issues->where('status', 'reported')->count(),
            'assigned' => $issues->where('status', 'assigned')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
        ];

        return view('pages.supervisor.issues', compact('issues', 'stats'));
    }

    public function assign(Request $request, Issue $issue)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $issue->transitionTo('assigned', auth()->id());
        $issue->update(['assigned_to' => $request->assigned_to]);

        $assignee = User::find($request->assigned_to);
        if ($assignee) {
            $assignee->notify(new IssueAssignedNotification($issue));
        }

        return back()->with('success', 'Issue assigned successfully.');
    }

    public function convertToTask(Request $request, Issue $issue)
    {
        abort_unless($issue->location && $this->canManage($issue->location, auth()->id()), 403);

        if ($issue->task) {
            return back()->with('error', 'This issue has already been converted to a task.');
        }

        if ($issue->status === 'reported' && !$issue->assigned_to) {
            return back()->with('error', 'Assign a worker to this issue before converting to a task.');
        }

        $request->validate([
            'category' => 'required|in:cleaning,maintenance,inspection,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date|after:today',
        ]);

        if ($issue->status === 'reported') {
            $issue->transitionTo('assigned', auth()->id());
        }

        $title = Str::limit($issue->description, 255);

        $task = Task::create([
            'title' => $title,
            'description' => $issue->description,
            'location' => $issue->location->name,
            'location_id' => $issue->location_id,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'pending',
            'supervisor_id' => auth()->id(),
            'assigned_to' => $issue->assigned_to,
            'issue_id' => $issue->id,
            'due_date' => $request->due_date,
        ]);

        return back()->with('success', 'Issue converted to task successfully. Task #' . $task->id . ' created.');
    }

    public function updateStatus(Request $request, Issue $issue)
    {
        $request->validate([
            'status' => 'required|in:reported,assigned,in_progress,resolved',
        ]);

        $issue->transitionTo($request->status, auth()->id());

        return back()->with('success', 'Issue status updated.');
    }

    public function reject(Request $request, Issue $issue)
    {
        abort_unless($issue->location && $this->canManage($issue->location, auth()->id()), 403);

        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);

        $issue->transitionTo('rejected', auth()->id(), $request->rejection_reason);

        return back()->with('success', 'Issue rejected.');
    }

    private function canManage(Location $location, int $userId): bool
    {
        $current = $location;
        while ($current) {
            if ($current->supervisor_id === $userId) return true;
            $current = $current->parent;
        }
        return false;
    }
}
