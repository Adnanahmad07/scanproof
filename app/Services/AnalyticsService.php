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
        $orgId = auth()->user()->organization_id;

        return [
            'totalStaff' => User::where('role', 'staff')->where('organization_id', $orgId)->count(),
            'activeTasks' => Task::where('organization_id', $orgId)->whereNotIn('status', [TaskStatus::Completed, TaskStatus::Verified])->count(),
            'totalLocations' => Location::where('organization_id', $orgId)->count(),
            'openIssues' => Issue::where('organization_id', $orgId)->whereNotIn('status', ['resolved', 'rejected'])->count(),
        ];
    }

    public function getReportData(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();
        $orgId = auth()->user()->organization_id;

        return [
            'period' => ['start' => $start, 'end' => $end],
            'tasks' => $this->getTaskStats($start, $end, $orgId),
            'issues' => $this->getIssueStats($start, $end, $orgId),
            'locations' => $this->getLocationStats($orgId),
            'resolutionTime' => $this->getAverageResolutionTime($start, $end, $orgId),
            'topLocations' => $this->getTopLocations($start, $end, $orgId),
            'byCategory' => $this->getTasksByCategory($start, $end, $orgId),
            'byPriority' => $this->getTasksByPriority($start, $end, $orgId),
        ];
    }

    private function getTaskStats(Carbon $start, Carbon $end, int $orgId): array
    {
        $tasks = Task::where('organization_id', $orgId)->whereBetween('created_at', [$start, $end]);

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

    private function getIssueStats(Carbon $start, Carbon $end, int $orgId): array
    {
        $issues = Issue::where('organization_id', $orgId)->whereBetween('created_at', [$start, $end]);

        return [
            'total' => (clone $issues)->count(),
            'reported' => (clone $issues)->where('status', 'reported')->count(),
            'assigned' => (clone $issues)->where('status', 'assigned')->count(),
            'in_progress' => (clone $issues)->where('status', 'in_progress')->count(),
            'resolved' => (clone $issues)->where('status', 'resolved')->count(),
            'rejected' => (clone $issues)->where('status', 'rejected')->count(),
        ];
    }

    private function getLocationStats(int $orgId): array
    {
        return [
            'total' => Location::where('organization_id', $orgId)->count(),
            'configured' => Location::where('organization_id', $orgId)->where('configured', true)->count(),
            'withIssues' => Location::where('organization_id', $orgId)->has('issues')->count(),
            'withActiveTasks' => Location::where('organization_id', $orgId)->has('activeTasks')->count(),
        ];
    }

    private function getAverageResolutionTime(Carbon $start, Carbon $end, int $orgId): ?float
    {
        $resolved = Issue::where('organization_id', $orgId)
            ->where('status', 'resolved')
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

    private function getTopLocations(Carbon $start, Carbon $end, int $orgId): Collection
    {
        return Location::query()
            ->where('organization_id', $orgId)
            ->select('locations.*')
            ->selectRaw('(select count(*) from issues where locations.id = issues.location_id and issues.created_at between ? and ?) as issues_count', [$start, $end])
            ->selectRaw('(select count(*) from tasks where locations.id = tasks.location_id and tasks.created_at between ? and ?) as tasks_count', [$start, $end])
            ->havingRaw('issues_count > 0 or tasks_count > 0')
            ->orderByDesc('issues_count')
            ->limit(5)
            ->get();
    }

    private function getTasksByCategory(Carbon $start, Carbon $end, int $orgId): array
    {
        return Task::where('organization_id', $orgId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    private function getTasksByPriority(Carbon $start, Carbon $end, int $orgId): array
    {
        return Task::where('organization_id', $orgId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();
    }
}
