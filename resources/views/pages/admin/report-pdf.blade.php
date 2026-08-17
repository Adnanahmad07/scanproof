<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ScanProof Report - {{ $startDate }} to {{ $endDate }}</title>
    <style>
        body { font-family: Inter, sans-serif; font-size: 12px; color: #1a1a2e; margin: 20px; }
        h1 { font-size: 24px; font-weight: 700; margin-bottom: 5px; }
        h2 { font-size: 16px; font-weight: 600; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; }
        .subtitle { color: #6b7280; font-size: 12px; margin-bottom: 20px; }
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .stat-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; }
        .stat-label { font-size: 11px; color: #6b7280; text-transform: uppercase; }
        .stat-value { font-size: 24px; font-weight: 700; margin-top: 5px; }
        .stat-sub { font-size: 10px; color: #6b7280; margin-top: 3px; }
        .health-score { text-align: center; padding: 20px; border: 2px solid #10b981; border-radius: 12px; margin-bottom: 20px; }
        .health-score .score { font-size: 48px; font-weight: 700; }
        .health-score .label { font-size: 14px; font-weight: 600; color: #10b981; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f3f4f6; text-align: left; padding: 8px 12px; font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; }
        td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <h1>ScanProof Facility Report</h1>
    <p class="subtitle">Period: {{ $startDate }} to {{ $endDate }}</p>

    {{-- Health Score --}}
    <div class="health-score">
        <div class="score">{{ $healthScore }}</div>
        <div class="label">{{ $healthLabel }} Health</div>
    </div>

    {{-- Summary Stats --}}
    <h2>Summary</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Tasks</div>
            <div class="stat-value">{{ $reportData['tasks']['total'] }}</div>
            <div class="stat-sub">{{ $reportData['tasks']['completed'] }} completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Issues</div>
            <div class="stat-value">{{ $reportData['issues']['total'] }}</div>
            <div class="stat-sub">{{ $reportData['issues']['resolved'] }} resolved</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Avg Resolution</div>
            <div class="stat-value">{{ $reportData['resolutionTime'] !== null ? $reportData['resolutionTime'] . 'h' : 'N/A' }}</div>
            <div class="stat-sub">hours to resolve</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Overdue Tasks</div>
            <div class="stat-value" style="color: {{ $reportData['tasks']['overdue'] > 0 ? '#ef4444' : '#10b981' }}">{{ $reportData['tasks']['overdue'] }}</div>
            <div class="stat-sub">past due date</div>
        </div>
    </div>

    {{-- Task Status --}}
    <h2>Task Status Breakdown</h2>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Pending</td><td>{{ $reportData['tasks']['pending'] }}</td></tr>
            <tr><td>In Progress</td><td>{{ $reportData['tasks']['in_progress'] }}</td></tr>
            <tr><td>Completed</td><td>{{ $reportData['tasks']['completed'] }}</td></tr>
            <tr><td>Overdue</td><td>{{ $reportData['tasks']['overdue'] }}</td></tr>
        </tbody>
    </table>

    {{-- Issue Status --}}
    <h2>Issue Status Breakdown</h2>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Reported</td><td>{{ $reportData['issues']['reported'] }}</td></tr>
            <tr><td>Assigned</td><td>{{ $reportData['issues']['assigned'] }}</td></tr>
            <tr><td>In Progress</td><td>{{ $reportData['issues']['in_progress'] }}</td></tr>
            <tr><td>Resolved</td><td>{{ $reportData['issues']['resolved'] }}</td></tr>
            <tr><td>Rejected</td><td>{{ $reportData['issues']['rejected'] }}</td></tr>
        </tbody>
    </table>

    {{-- Top Locations --}}
    @if ($reportData['topLocations']->isNotEmpty())
        <h2>Top Locations by Activity</h2>
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Issues</th>
                    <th>Tasks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reportData['topLocations'] as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td>{{ $location->issues_count }}</td>
                        <td>{{ $location->tasks_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Tasks by Category --}}
    @if (!empty($reportData['byCategory']))
        <h2>Tasks by Category</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reportData['byCategory'] as $category => $count)
                    <tr>
                        <td>{{ ucfirst($category) }}</td>
                        <td>{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Generated by ScanProof on {{ now()->format('M d, Y g:i A') }}</p>
    </div>
</body>
</html>
