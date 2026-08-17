@extends('layouts.admin')

@section('title', 'Reports - ScanProof')
@section('page-title', 'Reports')
@section('page-subtitle', 'Analytics and facility performance')

@section('content')
    <div class="space-y-6">

        {{-- Date Range Filter --}}
        <div class="bg-white rounded-2xl p-6 border border-surface-high">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-primary mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                           class="px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-primary mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                           class="px-4 py-2.5 bg-surface-low border border-surface-high rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.reports.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                   class="px-6 py-2.5 bg-error text-white rounded-xl text-sm font-medium hover:bg-error/90 transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download PDF
                </a>
            </form>
        </div>

        {{-- Facility Health Score --}}
        <div class="bg-white rounded-2xl p-6 border border-surface-high">
            <h3 class="text-lg font-semibold text-text-primary mb-4">Facility Health Score</h3>
            <div class="flex items-center gap-6">
                <div class="w-24 h-24 rounded-full border-4 border-{{ $healthScore >= 60 ? 'success' : ($healthScore >= 40 ? 'warning' : 'error') }} flex items-center justify-center">
                    <span class="text-2xl font-bold text-text-primary">{{ $healthScore }}</span>
                </div>
                <div>
                    <p class="text-lg font-semibold text-{{ $healthScore >= 60 ? 'success' : ($healthScore >= 40 ? 'warning' : 'error') }}">
                        {{ $healthLabel }}
                    </p>
                    <p class="text-sm text-text-muted mt-1">Based on task completion, issue resolution, and overdue rates</p>
                </div>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-surface-high">
                <p class="text-sm text-text-muted">Total Tasks</p>
                <p class="text-2xl font-bold text-text-primary mt-1">{{ $reportData['tasks']['total'] }}</p>
                <p class="text-xs text-success mt-1">{{ $reportData['tasks']['completed'] }} completed</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-surface-high">
                <p class="text-sm text-text-muted">Total Issues</p>
                <p class="text-2xl font-bold text-text-primary mt-1">{{ $reportData['issues']['total'] }}</p>
                <p class="text-xs text-success mt-1">{{ $reportData['issues']['resolved'] }} resolved</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-surface-high">
                <p class="text-sm text-text-muted">Avg Resolution Time</p>
                <p class="text-2xl font-bold text-text-primary mt-1">
                    {{ $reportData['resolutionTime'] !== null ? $reportData['resolutionTime'] . 'h' : 'N/A' }}
                </p>
                <p class="text-xs text-text-muted mt-1">hours to resolve</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-surface-high">
                <p class="text-sm text-text-muted">Overdue Tasks</p>
                <p class="text-2xl font-bold text-{{ $reportData['tasks']['overdue'] > 0 ? 'error' : 'text-primary' }} mt-1">
                    {{ $reportData['tasks']['overdue'] }}
                </p>
                <p class="text-xs text-text-muted mt-1">past due date</p>
            </div>
        </div>

        {{-- Task Status Breakdown --}}
        <div class="bg-white rounded-2xl p-6 border border-surface-high">
            <h3 class="text-lg font-semibold text-text-primary mb-4">Task Status Breakdown</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-warning/5 rounded-xl">
                    <p class="text-2xl font-bold text-warning">{{ $reportData['tasks']['pending'] }}</p>
                    <p class="text-xs text-text-muted mt-1">Pending</p>
                </div>
                <div class="text-center p-4 bg-info/5 rounded-xl">
                    <p class="text-2xl font-bold text-info">{{ $reportData['tasks']['in_progress'] }}</p>
                    <p class="text-xs text-text-muted mt-1">In Progress</p>
                </div>
                <div class="text-center p-4 bg-success/5 rounded-xl">
                    <p class="text-2xl font-bold text-success">{{ $reportData['tasks']['completed'] }}</p>
                    <p class="text-xs text-text-muted mt-1">Completed</p>
                </div>
                <div class="text-center p-4 bg-error/5 rounded-xl">
                    <p class="text-2xl font-bold text-error">{{ $reportData['tasks']['overdue'] }}</p>
                    <p class="text-xs text-text-muted mt-1">Overdue</p>
                </div>
            </div>
        </div>

        {{-- Issue Status Breakdown --}}
        <div class="bg-white rounded-2xl p-6 border border-surface-high">
            <h3 class="text-lg font-semibold text-text-primary mb-4">Issue Status Breakdown</h3>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div class="text-center p-4 bg-warning/5 rounded-xl">
                    <p class="text-2xl font-bold text-warning">{{ $reportData['issues']['reported'] }}</p>
                    <p class="text-xs text-text-muted mt-1">Reported</p>
                </div>
                <div class="text-center p-4 bg-info/5 rounded-xl">
                    <p class="text-2xl font-bold text-info">{{ $reportData['issues']['assigned'] }}</p>
                    <p class="text-xs text-text-muted mt-1">Assigned</p>
                </div>
                <div class="text-center p-4 bg-primary/5 rounded-xl">
                    <p class="text-2xl font-bold text-primary">{{ $reportData['issues']['in_progress'] }}</p>
                    <p class="text-xs text-text-muted mt-1">In Progress</p>
                </div>
                <div class="text-center p-4 bg-success/5 rounded-xl">
                    <p class="text-2xl font-bold text-success">{{ $reportData['issues']['resolved'] }}</p>
                    <p class="text-xs text-text-muted mt-1">Resolved</p>
                </div>
                <div class="text-center p-4 bg-error/5 rounded-xl">
                    <p class="text-2xl font-bold text-error">{{ $reportData['issues']['rejected'] }}</p>
                    <p class="text-xs text-text-muted mt-1">Rejected</p>
                </div>
            </div>
        </div>

        {{-- Top Locations --}}
        @if ($reportData['topLocations']->isNotEmpty())
            <div class="bg-white rounded-2xl p-6 border border-surface-high">
                <h3 class="text-lg font-semibold text-text-primary mb-4">Top Locations by Activity</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-surface-high">
                                <th class="text-left py-3 px-4 font-medium text-text-muted">Location</th>
                                <th class="text-left py-3 px-4 font-medium text-text-muted">Issues</th>
                                <th class="text-left py-3 px-4 font-medium text-text-muted">Tasks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData['topLocations'] as $location)
                                <tr class="border-b border-surface-high/50 hover:bg-surface-low/30">
                                    <td class="py-3 px-4 font-medium text-text-primary">{{ $location->name }}</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-warning/10 text-warning">
                                            {{ $location->issues_count }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-info/10 text-info">
                                            {{ $location->tasks_count }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Tasks by Category --}}
        @if (!empty($reportData['byCategory']))
            <div class="bg-white rounded-2xl p-6 border border-surface-high">
                <h3 class="text-lg font-semibold text-text-primary mb-4">Tasks by Category</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach ($reportData['byCategory'] as $category => $count)
                        <div class="text-center p-4 bg-surface-low rounded-xl">
                            <p class="text-2xl font-bold text-text-primary">{{ $count }}</p>
                            <p class="text-xs text-text-muted mt-1">{{ ucfirst($category) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Tasks by Priority --}}
        @if (!empty($reportData['byPriority']))
            <div class="bg-white rounded-2xl p-6 border border-surface-high">
                <h3 class="text-lg font-semibold text-text-primary mb-4">Tasks by Priority</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach ($reportData['byPriority'] as $priority => $count)
                        <div class="text-center p-4 bg-surface-low rounded-xl">
                            <p class="text-2xl font-bold text-text-primary">{{ $count }}</p>
                            <p class="text-xs text-text-muted mt-1">{{ ucfirst($priority) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
@endsection
