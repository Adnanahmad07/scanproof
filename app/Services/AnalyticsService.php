<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function getDashboardStats(): array
    {
        return [
            'totalStaff' => User::where('role', 'staff')->count(),
            'activeTasks' => Task::whereNotIn('status', [TaskStatus::Completed, TaskStatus::Verified])->count(),
            'totalLocations' => Location::count(),
            'openIssues' => Issue::whereNotIn('status', ['resolved', 'rejected'])->count(),
        ];
    }

    public function getReportData(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        return [
            'period' => ['start' => $start, 'end' => $end],
            'tasks' => $this->getTaskStats($start, $end),
            'issues' => $this->getIssueStats($start, $end),
            'locations' => $this->getLocationStats(),
            'resolutionTime' => $this->getAverageResolutionTime($start, $end),
            'topLocations' => $this->getTopLocations($start, $end),
            'byCategory' => $this->getTasksByCategory($start, $end),
            'byPriority' => $this->getTasksByPriority($start, $end),
        ];
    }

    private function getTaskStats(Carbon $start, Carbon $end): array
    {
        $tasks = Task::whereBetween('created_at', [$start, $end]);

        return [
            'total' => (clone $tasks)->count(),
            'completed' => (clone $tasks)->where('status', TaskStatus::Completed)->count(),
            'in_progress' => (clone $tasks)->where('status', TaskStatus::InProgress)->count(),
            'pending' => (clone $tasks)->where('status', TaskStatus::Pending)->count(),
            'overdue' => (clone $tasks)->where('due_date', '<', now())
                ->whereNotIn('status', [TaskStatus::Completed, TaskStatus::Verified])
                ->count(),
        ];
    }

    private function getIssueStats(Carbon $start, Carbon $end): array
    {
        $issues = Issue::whereBetween('created_at', [$start, $end]);

        return [
            'total' => (clone $issues)->count(),
            'reported' => (clone $issues)->where('status', 'reported')->count(),
            'assigned' => (clone $issues)->where('status', 'assigned')->count(),
            'in_progress' => (clone $issues)->where('status', 'in_progress')->count(),
            'resolved' => (clone $issues)->where('status', 'resolved')->count(),
            'rejected' => (clone $issues)->where('status', 'rejected')->count(),
        ];
    }

    private function getLocationStats(): array
    {
        return [
            'total' => Location::count(),
            'configured' => Location::where('configured', true)->count(),
            'withIssues' => Location::has('issues')->count(),
            'withActiveTasks' => Location::has('activeTasks')->count(),
        ];
    }

    private function getAverageResolutionTime(Carbon $start, Carbon $end): ?float
    {
        $resolved = Issue::where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($resolved->isEmpty()) {
            return null;
        }

        $totalHours = $resolved->sum(function ($issue) {
            return $issue->created_at->diffInHours($issue->resolved_at);
        });

        return round($totalHours / $resolved->count(), 1);
    }

    private function getTopLocations(Carbon $start, Carbon $end): Collection
    {
        return Location::query()
            ->select('locations.*')
            ->selectRaw('(select count(*) from issues where locations.id = issues.location_id and issues.created_at between ? and ?) as issues_count', [$start, $end])
            ->selectRaw('(select count(*) from tasks where locations.id = tasks.location_id and tasks.created_at between ? and ?) as tasks_count', [$start, $end])
            ->whereRaw('issues_count > 0 or tasks_count > 0')
            ->orderByDesc('issues_count')
            ->limit(5)
            ->get();
    }

    private function getTasksByCategory(Carbon $start, Carbon $end): array
    {
        return Task::whereBetween('created_at', [$start, $end])
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    private function getTasksByPriority(Carbon $start, Carbon $end): array
    {
        return Task::whereBetween('created_at', [$start, $end])
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();
    }
}
