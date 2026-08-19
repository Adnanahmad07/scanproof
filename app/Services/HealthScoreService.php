<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;

class HealthScoreService
{
    /**
     * Calculate facility health score (0-100).
     *
     * Formula:
     * - Task completion rate: 40% weight (completed+verified / total)
     * - Issue resolution rate: 30% weight (resolved / total, excluding rejected)
     * - Overdue penalty: 20% weight (1 - overdue/total tasks, min 0)
     * - Active issue penalty: 10% weight (1 - open issues / total issues, min 0)
     */
    public function calculate(): int
    {
        $orgId = auth()->user()->organization_id;

        $totalTasks = Task::where('organization_id', $orgId)->count();
        $completedTasks = Task::where('organization_id', $orgId)->whereIn('status', [TaskStatus::Completed, TaskStatus::Verified])->count();
        $overdueTasks = Task::where('organization_id', $orgId)
            ->where('due_date', '<', now())
            ->whereNotIn('status', [TaskStatus::Completed, TaskStatus::Verified])
            ->count();

        $totalIssues = Issue::where('organization_id', $orgId)->where('status', '!=', 'rejected')->count();
        $resolvedIssues = Issue::where('organization_id', $orgId)->where('status', 'resolved')->count();
        $openIssues = Issue::where('organization_id', $orgId)->whereNotIn('status', ['resolved', 'rejected'])->count();

        // Task completion rate (40%)
        $taskScore = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 40 : 40;

        // Issue resolution rate (30%)
        $issueScore = $totalIssues > 0 ? ($resolvedIssues / $totalIssues) * 30 : 30;

        // Overdue penalty (20%)
        $overdueScore = $totalTasks > 0 ? max(0, (1 - $overdueTasks / $totalTasks)) * 20 : 20;

        // Active issue penalty (10%)
        $activeIssueScore = $totalIssues > 0 ? max(0, (1 - $openIssues / $totalIssues)) * 10 : 10;

        return (int) round($taskScore + $issueScore + $overdueScore + $activeIssueScore);
    }

    public function getLabel(int $score): string
    {
        return match (true) {
            $score >= 80 => 'Excellent',
            $score >= 60 => 'Good',
            $score >= 40 => 'Fair',
            $score >= 20 => 'Poor',
            default => 'Critical',
        };
    }

    public function getColor(int $score): string
    {
        return match (true) {
            $score >= 80 => 'success',
            $score >= 60 => 'info',
            $score >= 40 => 'warning',
            default => 'error',
        };
    }
}
