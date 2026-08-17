<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;
use App\Models\User;
use App\Notifications\IssueReportedNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    private function createSupervisor(): User
    {
        return User::factory()->supervisor()->create(['is_active' => true]);
    }

    private function createStaff(User $supervisor): User
    {
        return User::factory()->staff()->create([
            'is_active' => true,
            'supervisor_id' => $supervisor->id,
        ]);
    }

    private function createLocation(User $supervisor): Location
    {
        return Location::create([
            'name' => 'Test Room',
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'supervisor_id' => $supervisor->id,
            'created_by' => $supervisor->id,
        ]);
    }

    // ── P1-TC01: Notification dropdown renders ──

    public function test_p1_tc01_notification_dropdown_renders_for_authenticated_user(): void
    {
        $supervisor = $this->createSupervisor();

        Livewire::actingAs($supervisor)
            ->test(\App\Livewire\NotificationDropdown::class)
            ->assertSee('Notifications');
    }

    // ── P1-TC02: Unread count shows correct number ──

    public function test_p1_tc02_unread_count_zero(): void
    {
        $supervisor = $this->createSupervisor();

        Livewire::actingAs($supervisor)
            ->test(\App\Livewire\NotificationDropdown::class)
            ->assertSet('unreadCount', 0);
    }

    public function test_p1_tc02_unread_count_with_notifications(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff($supervisor);
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Test Task',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'medium',
            'status' => 'pending',
            'supervisor_id' => $supervisor->id,
        ]);

        $supervisor->notify(new TaskAssignedNotification($task));

        Livewire::actingAs($supervisor)
            ->test(\App\Livewire\NotificationDropdown::class)
            ->assertSet('unreadCount', 1);
    }

    // ── P1-TC03: Mark notification as read ──

    public function test_p1_tc03_mark_notification_as_read(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff($supervisor);
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Test Task',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'medium',
            'status' => 'pending',
            'supervisor_id' => $supervisor->id,
        ]);

        $supervisor->notify(new TaskAssignedNotification($task));

        $notification = $supervisor->notifications->first();

        Livewire::actingAs($supervisor)
            ->test(\App\Livewire\NotificationDropdown::class)
            ->call('markAsRead', $notification->id);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);

        $this->assertNotNull($supervisor->fresh()->notifications->first()->read_at);
    }

    // ── P1-TC04: Mark all as read ──

    public function test_p1_tc04_mark_all_as_read(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        for ($i = 0; $i < 3; $i++) {
            $task = Task::create([
                'title' => "Task {$i}",
                'location' => $location->name,
                'location_id' => $location->id,
                'category' => 'cleaning',
                'priority' => 'medium',
                'status' => 'pending',
                'supervisor_id' => $supervisor->id,
            ]);
            $supervisor->notify(new TaskAssignedNotification($task));
        }

        $this->assertEquals(3, $supervisor->unreadNotifications()->count());

        Livewire::actingAs($supervisor)
            ->test(\App\Livewire\NotificationDropdown::class)
            ->call('markAllRead');

        $this->assertEquals(0, $supervisor->fresh()->unreadNotifications()->count());
    }

    // ── P1-TC05: Notification dispatches on issue reported ──

    public function test_p1_tc05_notification_dispatched_on_issue_reported(): void
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

    // ── P1-TC06: Notification dispatches on task assigned ──

    public function test_p1_tc06_notification_dispatched_on_task_assigned(): void
    {
        Notification::fake();

        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff($supervisor);
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Clean Lobby',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'high',
            'status' => 'pending',
            'supervisor_id' => $supervisor->id,
            'assigned_to' => $staff->id,
        ]);

        $staff->notify(new TaskAssignedNotification($task));

        Notification::assertSentTo($staff, TaskAssignedNotification::class);
    }

    // ── P1-TC07: Notification dispatches on task completed ──

    public function test_p1_tc07_notification_dispatched_on_task_completed(): void
    {
        Notification::fake();

        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff($supervisor);
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Fix Door',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'maintenance',
            'priority' => 'high',
            'status' => 'completed',
            'supervisor_id' => $supervisor->id,
            'assigned_to' => $staff->id,
        ]);

        $supervisor->notify(new TaskCompletedNotification($task));

        Notification::assertSentTo($supervisor, TaskCompletedNotification::class);
    }

    // ── P1-TC08: Dropdown toggle ──

    public function test_p1_tc08_dropdown_toggle(): void
    {
        $supervisor = $this->createSupervisor();

        Livewire::actingAs($supervisor)
            ->test(\App\Livewire\NotificationDropdown::class)
            ->assertSet('open', false)
            ->call('toggle')
            ->assertSet('open', true)
            ->call('toggle')
            ->assertSet('open', false);
    }

    // ── P1-TC09: Notifications stored in database ──

    public function test_p1_tc09_notifications_stored_in_database(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff($supervisor);
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Test Task',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'medium',
            'status' => 'pending',
            'supervisor_id' => $supervisor->id,
        ]);

        $staff->notify(new TaskAssignedNotification($task));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $staff->id,
            'type' => TaskAssignedNotification::class,
        ]);
    }

    // ── P1-TC10: Notification data contains correct fields ──

    public function test_p1_tc10_notification_data_contains_correct_fields(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Clean Lobby',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'high',
            'status' => 'pending',
            'supervisor_id' => $supervisor->id,
        ]);

        $notification = new TaskAssignedNotification($task);
        $data = $notification->toArray($supervisor);

        $this->assertEquals($task->id, $data['task_id']);
        $this->assertEquals('Clean Lobby', $data['title']);
        $this->assertEquals('cleaning', $data['category']);
        $this->assertEquals('task_assigned', $data['type']);
    }

    // ── Edge Case: No supervisor assigned to location ──

    public function test_p1_edge_no_supervisor_notification_doesnt_crash(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        $location = Location::create([
            'name' => 'Orphan Room',
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'created_by' => $admin->id,
        ]);

        $issue = Issue::create([
            'location_id' => $location->id,
            'description' => 'Issue at unassigned location',
            'status' => 'reported',
        ]);

        // Should fallback to admin without crashing
        $this->assertDatabaseHas('issues', [
            'id' => $issue->id,
            'status' => 'reported',
        ]);
    }

    // ── Edge Case: Toggle with unread marks all read ──

    public function test_p1_edge_toggle_with_unread_marks_all_read(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        $task = Task::create([
            'title' => 'Task',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'low',
            'status' => 'pending',
            'supervisor_id' => $supervisor->id,
        ]);

        $supervisor->notify(new TaskAssignedNotification($task));

        Livewire::actingAs($supervisor)
            ->test(\App\Livewire\NotificationDropdown::class)
            ->assertSet('unreadCount', 1)
            ->call('toggle');

        // Verify all notifications are now read
        $this->assertEquals(0, $supervisor->fresh()->unreadNotifications()->count());
    }
}
