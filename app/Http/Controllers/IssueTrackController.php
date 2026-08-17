<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use Illuminate\Http\Request;

class IssueTrackController extends Controller
{
    public function show(string $tracking_code)
    {
        $issue = Issue::where('tracking_code', $tracking_code)
            ->with(['location', 'events.actor'])
            ->firstOrFail();

        return view('pages.scan.tracking', ['issue' => $issue]);
    }
}
