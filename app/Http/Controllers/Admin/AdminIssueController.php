<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Issue;

class AdminIssueController extends Controller
{
    public function index()
    {
        $issues = Issue::where('organization_id', auth()->user()->organization_id)
            ->with(['location', 'assignee', 'reporter', 'task'])
            ->latest()
            ->get();

        $stats = [
            'total' => $issues->count(),
            'reported' => $issues->where('status', 'reported')->count(),
            'assigned' => $issues->where('status', 'assigned')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
        ];

        return view('pages.admin.issues', compact('issues', 'stats'));
    }
}
