<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\DailyReportNotification;
use App\Services\AnalyticsService;
use App\Services\HealthScoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDailyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(AnalyticsService $analytics, HealthScoreService $healthScore): void
    {
        $admins = User::where('role', 'admin')
            ->whereNotNull('organization_id')
            ->get();

        foreach ($admins as $admin) {
            // Set the authenticated user context for organization-scoped services
            auth()->login($admin);

            $reportData = $analytics->getReportData(
                now()->subDay()->format('Y-m-d'),
                now()->format('Y-m-d')
            );

            $score = $healthScore->calculate();

            $admin->notify(new DailyReportNotification($reportData, $score));
        }

        auth()->logout();
    }
}
