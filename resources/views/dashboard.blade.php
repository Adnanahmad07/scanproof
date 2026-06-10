@extends('layouts.admin')

@section('title', 'Dashboard - ScanProof')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your facility')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Overview</h1>
            <p class="text-sm text-text-secondary mt-1">Real-time facility status and scanning metrics.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2.5 text-sm font-medium text-text-primary bg-white border border-surface-high rounded-xl hover:bg-surface-low transition-colors">
                Export Report
            </button>
            <button class="px-4 py-2.5 text-sm font-medium text-white bg-primary rounded-xl hover:bg-primary-dark transition-colors shadow-sm">
                New Scan Area
            </button>
        </div>
    </div>

    {{-- Stats Grid --}}
    {{-- DUMMY DATA: Remove these hardcoded values and replace with real data --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Completed --}}
        <div class="bg-white rounded-xl border border-surface-high p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-text-secondary">Completed</p>
                <div class="w-10 h-10 bg-success/10 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-end gap-2">
                <p class="text-3xl font-bold text-text-primary">48</p>
                <span class="text-xs font-medium text-success mb-1">↑ 12%</span>
            </div>
        </div>

        {{-- Overdue --}}
        <div class="bg-white rounded-xl border border-surface-high p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-text-secondary">Overdue</p>
                <div class="w-10 h-10 bg-error/10 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-end gap-2">
                <p class="text-3xl font-bold text-text-primary">3</p>
                <span class="text-xs font-medium text-error mb-1">↑ 2</span>
            </div>
        </div>

        {{-- Issues Reported --}}
        <div class="bg-white rounded-xl border border-surface-high p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-text-secondary">Issues Reported</p>
                <div class="w-10 h-10 bg-warning/10 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-end gap-2">
                <p class="text-3xl font-bold text-text-primary">2</p>
                <span class="text-xs font-medium text-text-secondary mb-1">Needs review</span>
            </div>
        </div>

        {{-- Total Tasks --}}
        <div class="bg-white rounded-xl border border-surface-high p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-text-secondary">Total Tasks</p>
                <div class="w-10 h-10 bg-info/10 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-end gap-2">
                <p class="text-3xl font-bold text-text-primary">53</p>
                <span class="text-xs font-medium text-text-secondary mb-1">Today</span>
            </div>
        </div>
    </div>

    {{-- Weekly Completion Trend --}}
    {{-- DUMMY DATA: Replace chart with real data from backend --}}
    <div class="bg-white rounded-xl border border-surface-high p-5 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-text-primary">Weekly Completion Trend</h2>
            <button class="p-2 text-text-muted hover:text-text-secondary hover:bg-surface-container rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </button>
        </div>
        <div class="h-48 flex items-end justify-between gap-2 px-2">
            {{-- DUMMY DATA: Bar heights represent weekly completion data --}}
            <div class="flex flex-col items-center gap-2 flex-1">
                <div class="w-full bg-primary/20 rounded-t-lg" style="height: 60%"></div>
                <span class="text-xs text-text-muted">Mon</span>
            </div>
            <div class="flex flex-col items-center gap-2 flex-1">
                <div class="w-full bg-primary/40 rounded-t-lg" style="height: 45%"></div>
                <span class="text-xs text-text-muted">Tue</span>
            </div>
            <div class="flex flex-col items-center gap-2 flex-1">
                <div class="w-full bg-primary/60 rounded-t-lg" style="height: 75%"></div>
                <span class="text-xs text-text-muted">Wed</span>
            </div>
            <div class="flex flex-col items-center gap-2 flex-1">
                <div class="w-full bg-primary/80 rounded-t-lg" style="height: 50%"></div>
                <span class="text-xs text-text-muted">Thu</span>
            </div>
            <div class="flex flex-col items-center gap-2 flex-1">
                <div class="w-full bg-primary rounded-t-lg" style="height: 85%"></div>
                <span class="text-xs text-text-muted">Fri</span>
            </div>
            <div class="flex flex-col items-center gap-2 flex-1">
                <div class="w-full bg-primary/70 rounded-t-lg" style="height: 65%"></div>
                <span class="text-xs text-text-muted">Sat</span>
            </div>
            <div class="flex flex-col items-center gap-2 flex-1">
                <div class="w-full bg-primary/30 rounded-t-lg" style="height: 30%"></div>
                <span class="text-xs text-text-muted">Sun</span>
            </div>
        </div>
    </div>

    {{-- Bottom Section: Recent Activity & Location Performance --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Activity --}}
        {{-- DUMMY DATA: Replace with real activity data from backend --}}
        <div class="bg-white rounded-xl border border-surface-high p-5 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-text-primary">Recent Activity</h2>
                <a href="#" class="text-sm font-medium text-primary hover:text-primary-dark transition-colors">View All</a>
            </div>
            <div class="space-y-4">
                {{-- Activity Item 1 --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-success/10 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-text-primary">
                            <span class="font-semibold">Sarah Jenkins</span> completed scan at <span class="font-semibold">HVAC Room B</span>
                        </p>
                        <p class="text-xs text-text-muted mt-0.5">2 minutes ago • Routine Check</p>
                    </div>
                    <span class="shrink-0 text-xs font-medium text-success bg-success/10 px-2.5 py-1 rounded-full">Verified</span>
                </div>

                {{-- Activity Item 2 --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-error/10 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-text-primary">
                            <span class="font-semibold">Mike Ross</span> flagged an issue at <span class="font-semibold">Main Lobby Exit</span>
                        </p>
                        <p class="text-xs text-text-muted mt-0.5">15 minutes ago • Door Sensor Malfunction</p>
                    </div>
                    <span class="shrink-0 text-xs font-medium text-error bg-error/10 px-2.5 py-1 rounded-full">Action Required</span>
                </div>

                {{-- Activity Item 3 --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-success/10 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-text-primary">
                            <span class="font-semibold">David Chen</span> completed scan at <span class="font-semibold">Server Room 1</span>
                        </p>
                        <p class="text-xs text-text-muted mt-0.5">45 minutes ago • Security Sweep</p>
                    </div>
                    <span class="shrink-0 text-xs font-medium text-success bg-success/10 px-2.5 py-1 rounded-full">Verified</span>
                </div>

                {{-- Activity Item 4 --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-text-primary">
                            <span class="font-semibold">Emma Wilson</span> started task at <span class="font-semibold">Parking Level B2</span>
                        </p>
                        <p class="text-xs text-text-muted mt-0.5">1 hour ago • Floor Maintenance</p>
                    </div>
                    <span class="shrink-0 text-xs font-medium text-primary bg-primary/10 px-2.5 py-1 rounded-full">In Progress</span>
                </div>

                {{-- Activity Item 5 --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-warning/10 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-text-primary">
                            <span class="font-semibold">John Davis</span> reported issue at <span class="font-semibold">North Wing Stairwell</span>
                        </p>
                        <p class="text-xs text-text-muted mt-0.5">2 hours ago • Lighting Problem</p>
                    </div>
                    <span class="shrink-0 text-xs font-medium text-warning bg-warning/10 px-2.5 py-1 rounded-full">Pending Review</span>
                </div>
            </div>
        </div>

        {{-- Location Performance --}}
        {{-- DUMMY DATA: Replace with real location performance data from backend --}}
        <div class="bg-white rounded-xl border border-surface-high p-5 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-text-primary">Location Performance</h2>
                <button class="p-2 text-text-muted hover:text-text-secondary hover:bg-surface-container rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </button>
            </div>
            <div class="space-y-5">
                {{-- Location 1: North Wing --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-text-primary">North Wing</span>
                        <span class="text-sm font-semibold text-success">98%</span>
                    </div>
                    <div class="w-full h-2 bg-surface-container rounded-full overflow-hidden">
                        <div class="h-full bg-success rounded-full" style="width: 98%"></div>
                    </div>
                    <p class="text-xs text-text-muted mt-1">49/50 tasks</p>
                </div>

                {{-- Location 2: South Wing --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-text-primary">South Wing</span>
                        <span class="text-sm font-semibold text-success">85%</span>
                    </div>
                    <div class="w-full h-2 bg-surface-container rounded-full overflow-hidden">
                        <div class="h-full bg-success rounded-full" style="width: 85%"></div>
                    </div>
                    <p class="text-xs text-text-muted mt-1">34/40 tasks</p>
                </div>

                {{-- Location 3: East Annex --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-text-primary">East Annex</span>
                        <span class="text-sm font-semibold text-error">45%</span>
                    </div>
                    <div class="w-full h-2 bg-surface-container rounded-full overflow-hidden">
                        <div class="h-full bg-error rounded-full" style="width: 45%"></div>
                    </div>
                    <p class="text-xs text-error mt-1">9/20 tasks • Requires Attention</p>
                </div>

                {{-- Location 4: Parking Structure A --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-text-primary">Parking Structure A</span>
                        <span class="text-sm font-semibold text-success">100%</span>
                    </div>
                    <div class="w-full h-2 bg-surface-container rounded-full overflow-hidden">
                        <div class="h-full bg-success rounded-full" style="width: 100%"></div>
                    </div>
                    <p class="text-xs text-text-muted mt-1">25/25 tasks</p>
                </div>

                {{-- Location 5: Main Lobby --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-text-primary">Main Lobby</span>
                        <span class="text-sm font-semibold text-warning">72%</span>
                    </div>
                    <div class="w-full h-2 bg-surface-container rounded-full overflow-hidden">
                        <div class="h-full bg-warning rounded-full" style="width: 72%"></div>
                    </div>
                    <p class="text-xs text-text-muted mt-1">18/25 tasks</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
