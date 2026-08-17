<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\HealthScoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $reportData,
        public int $healthScore,
    ) {}

    public function via(User $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $tasks = $this->reportData['tasks'];
        $issues = $this->reportData['issues'];

        return (new MailMessage)
            ->subject('Daily Facility Report - ' . now()->format('M d, Y'))
            ->line('Here is your daily facility performance summary.')
            ->line("Health Score: {$this->healthScore}/100")
            ->line("Tasks: {$tasks['total']} total, {$tasks['completed']} completed, {$tasks['overdue']} overdue")
            ->line("Issues: {$issues['total']} total, {$issues['resolved']} resolved, {$issues['reported']} new")
            ->when($this->reportData['resolutionTime'] !== null, fn ($m) => $m->line("Avg Resolution Time: {$this->reportData['resolutionTime']}h"))
            ->action('View Full Report', url('/admin/reports'));
    }

    public function toArray(User $notifiable): array
    {
        return [
            'report_data' => $this->reportData,
            'health_score' => $this->healthScore,
            'type' => 'daily_report',
            'date' => now()->format('Y-m-d'),
        ];
    }
}
