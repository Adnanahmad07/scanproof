<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Jobs\SendDailyReport;
use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;
use App\Models\User;
use App\Notifications\DailyReportNotification;
use App\Notifications\IssueAssignedNotification;
use App\Notifications\IssueReportedNotification;
use App\Notifications\IssueResolvedNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;
use App\Notifications\TaskOverdueNotification;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class Phase7NotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function createSupervisor(): User
    {
        return User::factory()->create(['role' => 'supervisor', 'is_active' => true]);
    }

    private function createStaff(): User
    {
        return User::factory()->create(['role' => 'staff', 'is_active' => true]);
    }

    private function createLocation(User $supervisor): Location
    {
        return Location::create([
            'name' => 'Test Room',
            'type' => 'room',
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'supervisor_id' => $supervisor->id,
            'created_by' => $supervisor->id,
        ]);
    }

    // ── F7.1: Email notifications (assignment, completion, etc.) ──

    // TC-7.1: Mail queued on event (Notification::fake)

    public function test_tc7_1_task_assigned_notification_queued(): void
    {
        Notification::fake();

        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Clean the lobby',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'medium',
            'status' => TaskStatus::Pending,
            'supervisor_id' => $supervisor->id,
        ]);

        $staff->notify(new TaskAssignedNotification($task));

        Notification::assertSentTo($staff, TaskAssignedNotification::class);
    }

    public function test_tc7_1_task_completed_notification_queued(): void
    {
        Notification::fake();

        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Fix the door',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'maintenance',
            'priority' => 'high',
            'status' => TaskStatus::Completed,
            'supervisor_id' => $supervisor->id,
            'assigned_to' => $staff->id,
        ]);

        $supervisor->notify(new TaskCompletedNotification($task));

        Notification::assertSentTo($supervisor, TaskCompletedNotification::class);
    }

    public function test_tc7_1_task_overdue_notification_queued(): void
    {
        Notification::fake();

        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Overdue task',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'urgent',
            'status' => TaskStatus::Pending,
            'supervisor_id' => $supervisor->id,
            'assigned_to' => $staff->id,
            'due_date' => now()->subDays(3),
        ]);

        $supervisor->notify(new TaskOverdueNotification($task));

        Notification::assertSentTo($supervisor, TaskOverdueNotification::class);
    }

    public function test_tc7_1_issue_reported_notification_queued(): void
    {
        Notification::fake();

        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        $issue = Issue::create([
            'location_id' => $location->id,
            'description' => 'Broken window in the conference room',
            'status' => 'reported',
        ]);

        $supervisor->notify(new IssueReportedNotification($issue));

        Notification::assertSentTo($supervisor, IssueReportedNotification::class);
    }

    public function test_tc7_1_issue_assigned_notification_queued(): void
    {
        Notification::fake();

        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);

        $issue = Issue::create([
            'location_id' => $location->id,
            'description' => 'Leaking faucet in the kitchen',
            'status' => 'assigned',
            'assigned_to' => $staff->id,
        ]);

        $staff->notify(new IssueAssignedNotification($issue));

        Notification::assertSentTo($staff, IssueAssignedNotification::class);
    }

    public function test_tc7_1_issue_resolved_notification_queued(): void
    {
        Notification::fake();

        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);

        $issue = Issue::create([
            'location_id' => $location->id,
            'description' => 'Fixed the leaking faucet',
            'status' => 'resolved',
            'assigned_to' => $staff->id,
            'resolved_at' => now(),
        ]);

        $supervisor->notify(new IssueResolvedNotification($issue));

        Notification::assertSentTo($supervisor, IssueResolvedNotification::class);
    }

    // TC-7.1: Notifications are queued (ShouldQueue interface)

    public function test_tc7_1_notifications_implement_should_queue(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Test task',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'low',
            'status' => TaskStatus::Pending,
            'supervisor_id' => $supervisor->id,
        ]);

        $issue = Issue::create([
            'location_id' => $location->id,
            'description' => 'Test issue for notification queue check',
            'status' => 'reported',
        ]);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new TaskAssignedNotification($task));
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new TaskCompletedNotification($task));
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new TaskOverdueNotification($task));
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new IssueReportedNotification($issue));
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new IssueAssignedNotification($issue));
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new IssueResolvedNotification($issue));
    }

    // TC-7.1: Notification contains correct data

    public function test_tc7_1_task_assigned_notification_contains_data(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Clean the lobby',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'medium',
            'status' => TaskStatus::Pending,
            'supervisor_id' => $supervisor->id,
        ]);

        $notification = new TaskAssignedNotification($task);
        $mail = $notification->toMail($staff);

        $this->assertStringContainsString('Clean the lobby', $mail->subject);
        $this->assertStringContainsString('Test Room', implode(' ', $mail->introLines));
    }

    public function test_tc7_1_task_completed_notification_contains_data(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Fix the door',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'maintenance',
            'priority' => 'high',
            'status' => TaskStatus::Completed,
            'supervisor_id' => $supervisor->id,
            'assigned_to' => $staff->id,
        ]);

        $notification = new TaskCompletedNotification($task);
        $mail = $notification->toMail($supervisor);

        $this->assertStringContainsString('Fix the door', $mail->subject);
    }

    // TC-7.1: Database notifications are stored

    public function test_tc7_1_database_notifications_stored(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Test task',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'low',
            'status' => TaskStatus::Pending,
            'supervisor_id' => $supervisor->id,
        ]);

        $staff->notify(new TaskAssignedNotification($task));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $staff->id,
            'type' => TaskAssignedNotification::class,
        ]);
    }

    // ── F7.2: WhatsApp integration ──

    public function test_tc7_2_whatsapp_service_can_be_mocked(): void
    {
        $mock = \Mockery::mock(WhatsAppService::class);
        $mock->shouldReceive('sendMessage')
            ->once()
            ->with('+1234567890', \Mockery::type('string'))
            ->andReturn(true);

        $this->app->instance(WhatsAppService::class, $mock);

        $service = app(WhatsAppService::class);
        $result = $service->sendMessage('+1234567890', 'Test message');

        $this->assertTrue($result);
    }

    public function test_tc7_2_whatsapp_service_returns_false_without_api_key(): void
    {
        // Without API key configured, should return false
        $service = new WhatsAppService();
        $result = $service->sendMessage('+1234567890', 'Test message');

        $this->assertFalse($result);
    }

    // ── F7.3: Automated daily reports ──

    public function test_tc7_3_daily_report_job_implements_should_queue(): void
    {
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new SendDailyReport());
    }

    public function test_tc7_3_daily_report_job_dispatched(): void
    {
        Queue::fake();

        SendDailyReport::dispatch();

        Queue::assertPushed(SendDailyReport::class);
    }

    public function test_tc7_3_daily_report_notification_queued(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        $reportData = [
            'period' => ['start' => now()->subDay(), 'end' => now()],
            'tasks' => ['total' => 5, 'completed' => 3, 'in_progress' => 1, 'pending' => 1, 'overdue' => 0],
            'issues' => ['total' => 2, 'reported' => 1, 'assigned' => 0, 'in_progress' => 0, 'resolved' => 1, 'rejected' => 0],
            'resolutionTime' => 24.5,
        ];

        $admin->notify(new DailyReportNotification($reportData, 85));

        Notification::assertSentTo($admin, DailyReportNotification::class);
    }

    public function test_tc7_3_daily_report_notification_contains_data(): void
    {
        $admin = $this->createAdmin();

        $reportData = [
            'period' => ['start' => now()->subDay(), 'end' => now()],
            'tasks' => ['total' => 10, 'completed' => 7, 'in_progress' => 2, 'pending' => 1, 'overdue' => 0],
            'issues' => ['total' => 3, 'reported' => 1, 'assigned' => 1, 'in_progress' => 0, 'resolved' => 1, 'rejected' => 0],
            'resolutionTime' => 18.3,
        ];

        $notification = new DailyReportNotification($reportData, 72);
        $mail = $notification->toMail($admin);

        $this->assertStringContainsString('Daily Facility Report', $mail->subject);
        $this->assertStringContainsString('72/100', implode(' ', $mail->introLines));
    }

    // ── Notifications via() includes database channel ──

    public function test_notifications_include_database_channel(): void
    {
        $admin = $this->createAdmin();
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Test',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'low',
            'status' => TaskStatus::Pending,
            'supervisor_id' => $supervisor->id,
        ]);

        $issue = Issue::create([
            'location_id' => $location->id,
            'description' => 'Test issue',
            'status' => 'reported',
        ]);

        $this->assertContains('database', (new TaskAssignedNotification($task))->via($staff));
        $this->assertContains('database', (new TaskCompletedNotification($task))->via($supervisor));
        $this->assertContains('database', (new TaskOverdueNotification($task))->via($supervisor));
        $this->assertContains('database', (new IssueReportedNotification($issue))->via($supervisor));
        $this->assertContains('database', (new IssueAssignedNotification($issue))->via($staff));
        $this->assertContains('database', (new IssueResolvedNotification($issue))->via($supervisor));
        $this->assertContains('database', (new DailyReportNotification(['tasks' => [], 'issues' => []], 100))->via($admin));
    }
}
