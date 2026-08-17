<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Models\Issue;
use App\Models\Location;
use App\Models\User;
use App\Notifications\IssueAssignedNotification;
use App\Notifications\IssueResolvedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class IssueNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $supervisor;
    protected User $staff;
    protected Location $location;
    protected Issue $issue;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->admin = User::factory()->admin()->create();
        $this->supervisor = User::factory()->supervisor()->create();
        $this->staff = User::factory()->staff()->create([
            'supervisor_id' => $this->supervisor->id,
        ]);

        $this->location = Location::create([
            'name' => 'Test Room',
            'type' => LocationType::Room,
            'building' => 'Test Building',
            'configured' => true,
            'configured_by' => $this->supervisor->id,
            'configured_at' => now(),
            'created_by' => $this->admin->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $this->issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'The light bulb is broken in the meeting room',
            'status' => 'reported',
        ]);
    }

    // TC-P2.1: Staff receives notification on issue assignment
    public function test_staff_receives_notification_on_assignment(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.assign', $this->issue), [
                'assigned_to' => $this->staff->id,
            ]);

        Notification::assertSentTo(
            $this->staff,
            IssueAssignedNotification::class
        );
    }

    // TC-P2.2: Supervisor receives notification on issue resolution
    public function test_supervisor_receives_notification_on_resolution(): void
    {
        $this->issue->update(['status' => 'assigned', 'assigned_to' => $this->staff->id]);

        $this->actingAs($this->staff)
            ->post(route('staff.issues.status', $this->issue), [
                'status' => 'in_progress',
            ]);

        Notification::assertNotSentTo(
            $this->supervisor,
            IssueResolvedNotification::class
        );

        $this->actingAs($this->staff)
            ->post(route('staff.issues.status', $this->issue), [
                'status' => 'resolved',
            ]);

        Notification::assertSentTo(
            $this->supervisor,
            IssueResolvedNotification::class
        );
    }

    // TC-P2.3: Notification not sent to self (assigner doesn't get assign email)
    public function test_supervisor_does_not_receive_own_assignment_notification(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.assign', $this->issue), [
                'assigned_to' => $this->staff->id,
            ]);

        Notification::assertNotSentTo(
            $this->supervisor,
            IssueAssignedNotification::class
        );
    }

    // TC-P2.4: Notification contains tracking code
    public function test_assignment_notification_contains_tracking_code(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.assign', $this->issue), [
                'assigned_to' => $this->staff->id,
            ]);

        Notification::assertSentTo(
            $this->staff,
            IssueAssignedNotification::class,
            function ($notification) {
                $mail = $notification->toMail($this->staff);
                return str_contains($mail->subject, $this->issue->tracking_code)
                    || str_contains(implode(' ', $mail->introLines), $this->issue->tracking_code);
            }
        );
    }

    // TC-P2.5: Notification contains location name
    public function test_assignment_notification_contains_location_name(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.assign', $this->issue), [
                'assigned_to' => $this->staff->id,
            ]);

        Notification::assertSentTo(
            $this->staff,
            IssueAssignedNotification::class,
            function ($notification) {
                $mail = $notification->toMail($this->staff);
                return str_contains($mail->subject, 'Test Room')
                    || str_contains(implode(' ', $mail->introLines), 'Test Room');
            }
        );
    }

    // TC-P2.6: Notifications are queued (not synchronous)
    public function test_notifications_are_queued(): void
    {
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new IssueAssignedNotification($this->issue));
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, new IssueResolvedNotification($this->issue));
    }
}
