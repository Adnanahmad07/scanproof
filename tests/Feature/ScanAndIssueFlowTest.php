<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Models\Issue;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScanAndIssueFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $supervisor;
    protected User $staff;
    protected Location $configuredLocation;
    protected Location $blankLocation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->supervisor = User::factory()->supervisor()->create();
        $this->staff = User::factory()->staff()->create();

        // Configured location (already set up by supervisor)
        $this->configuredLocation = Location::create([
            'name' => 'Room 101',
            'type' => LocationType::Lobby,
            'building' => 'Main Building',
            'configured' => true,
            'configured_by' => $this->supervisor->id,
            'configured_at' => now(),
            'created_by' => $this->admin->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        // Blank location (QR printed but not configured)
        $this->blankLocation = Location::create([
            'name' => 'Blank QR',
            'type' => LocationType::Room,
            'building' => 'Main Building',
            'configured' => false,
            'created_by' => $this->admin->id,
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    // ── PUBLIC SCAN ──

    public function test_scan_configured_location_shows_room_info(): void
    {
        $response = $this->get(route('scan.show', $this->configuredLocation->uuid));

        $response->assertStatus(200);
        $response->assertSee('Room 101');
        $response->assertSee('Lobby');
        $response->assertSee('Report Issue');
    }

    public function test_scan_blank_location_shows_room_info_for_anonymous(): void
    {
        $response = $this->get(route('scan.show', $this->blankLocation->uuid));

        $response->assertStatus(200);
        $response->assertSee('Blank QR');
        $response->assertSee('Report Issue');
    }

    public function test_scan_invalid_uuid_returns_404(): void
    {
        $response = $this->get(route('scan.show', 'invalid-uuid'));

        $response->assertNotFound();
    }

    public function test_scan_configured_location_shows_correct_type_icon(): void
    {
        $response = $this->get(route('scan.show', $this->configuredLocation->uuid));

        $response->assertSee($this->configuredLocation->type->label());
    }

    // ── ISSUE REPORTING ──

    public function test_anonymous_can_see_report_form(): void
    {
        $response = $this->get(route('scan.report', $this->configuredLocation->uuid));

        $response->assertStatus(200);
        $response->assertSee('Report Issue');
        $response->assertSee('Room 101');
    }

    public function test_anonymous_can_submit_issue(): void
    {
        Storage::fake('public');

        $response = $this->post(route('scan.report.submit', $this->configuredLocation->uuid), [
            'description' => 'The light bulb is broken in the lobby',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('issues', [
            'location_id' => $this->configuredLocation->id,
            'description' => 'The light bulb is broken in the lobby',
            'status' => 'reported',
        ]);

        $issue = Issue::where('location_id', $this->configuredLocation->id)->first();
        $this->assertStringStartsWith('SP-', $issue->tracking_code);
    }

    public function test_issue_requires_description(): void
    {
        $response = $this->post(route('scan.report.submit', $this->configuredLocation->uuid), [
            'description' => '',
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_issue_description_requires_min_length(): void
    {
        $response = $this->post(route('scan.report.submit', $this->configuredLocation->uuid), [
            'description' => 'Short',
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_issue_with_photo(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('issue.jpg', 200, 200);

        $response = $this->post(route('scan.report.submit', $this->configuredLocation->uuid), [
            'description' => 'There is a water leak in the bathroom',
            'photo' => $photo,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('issues', [
            'location_id' => $this->configuredLocation->id,
            'photo_path' => 'issues/' . $photo->hashName(),
        ]);
    }

    public function test_can_report_issue_on_any_location(): void
    {
        $response = $this->get(route('scan.report', $this->blankLocation->uuid));

        $response->assertStatus(200);
        $response->assertSee('Report Issue');
        $response->assertSee('Blank QR');
    }

    // ── TRACKING ──

    public function test_can_track_issue_by_code(): void
    {
        $issue = Issue::create([
            'tracking_code' => 'SP-TEST12',
            'location_id' => $this->configuredLocation->id,
            'description' => 'Test issue for tracking',
            'status' => 'reported',
        ]);

        $response = $this->get(route('track.show', 'SP-TEST12'));

        $response->assertStatus(200);
        $response->assertSee('SP-TEST12');
        $response->assertSee('Room 101');
        $response->assertSee('Reported');
    }

    public function test_invalid_tracking_code_returns_404(): void
    {
        $response = $this->get(route('track.show', 'SP-INVALID'));

        $response->assertNotFound();
    }

    // ── ISSUE MANAGEMENT (Supervisor) ──

    public function test_supervisor_can_see_issues_dashboard(): void
    {
        Issue::create([
            'location_id' => $this->configuredLocation->id,
            'description' => 'Test issue',
            'status' => 'reported',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.issues.index'));

        $response->assertStatus(200);
        $response->assertSee('Test issue');
    }

    public function test_supervisor_can_assign_worker(): void
    {
        $issue = Issue::create([
            'location_id' => $this->configuredLocation->id,
            'description' => 'Test issue to assign',
            'status' => 'reported',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.assign', $issue->id), [
                'assigned_to' => $this->staff->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('issues', [
            'id' => $issue->id,
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);
    }

    public function test_non_supervisor_cannot_access_supervisor_issues(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('supervisor.issues.index'));

        $response->assertForbidden();
    }

    // ── STAFF ISSUE VIEW ──

    public function test_staff_can_see_assigned_issues(): void
    {
        $issue = Issue::create([
            'location_id' => $this->configuredLocation->id,
            'description' => 'Assigned issue',
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.issues.index'));

        $response->assertStatus(200);
        $response->assertSee('Assigned issue');
    }

    public function test_staff_can_start_working_on_issue(): void
    {
        $issue = Issue::create([
            'location_id' => $this->configuredLocation->id,
            'description' => 'Issue to start',
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->post(route('staff.issues.status', $issue->id), [
                'status' => 'in_progress',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('issues', [
            'id' => $issue->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_staff_can_complete_issue(): void
    {
        $issue = Issue::create([
            'location_id' => $this->configuredLocation->id,
            'description' => 'Issue to complete',
            'status' => 'in_progress',
            'assigned_to' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->post(route('staff.issues.status', $issue->id), [
                'status' => 'resolved',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('issues', [
            'id' => $issue->id,
            'status' => 'resolved',
        ]);
    }

    public function test_staff_cannot_see_other_staff_issues(): void
    {
        $otherStaff = User::factory()->staff()->create();

        $issue = Issue::create([
            'location_id' => $this->configuredLocation->id,
            'description' => 'Not my issue',
            'status' => 'assigned',
            'assigned_to' => $otherStaff->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.issues.show', $issue->id));

        $response->assertForbidden();
    }

    // ── TRACKING CODE UNIQUENESS ──

    public function test_tracking_code_is_unique(): void
    {
        $issue1 = Issue::create([
            'location_id' => $this->configuredLocation->id,
            'description' => 'First issue',
        ]);

        $issue2 = Issue::create([
            'location_id' => $this->configuredLocation->id,
            'description' => 'Second issue',
        ]);

        $this->assertNotEquals($issue1->tracking_code, $issue2->tracking_code);
        $this->assertStringStartsWith('SP-', $issue1->tracking_code);
        $this->assertStringStartsWith('SP-', $issue2->tracking_code);
    }

    // ── ISSUE EVENTS (Audit Trail) ──

    public function test_status_change_creates_event(): void
    {
        $issue = Issue::create([
            'location_id' => $this->configuredLocation->id,
            'description' => 'Issue with events',
            'status' => 'reported',
        ]);

        $issue->transitionTo('assigned', $this->supervisor->id, 'Assigned to John');

        $this->assertDatabaseHas('issue_events', [
            'issue_id' => $issue->id,
            'from_status' => 'reported',
            'to_status' => 'assigned',
            'actor_id' => $this->supervisor->id,
            'note' => 'Assigned to John',
        ]);
    }

    public function test_invalid_status_transition_throws(): void
    {
        $issue = Issue::create([
            'location_id' => $this->configuredLocation->id,
            'description' => 'Cannot go directly to resolved',
            'status' => 'reported',
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $issue->transitionTo('resolved', $this->supervisor->id);
    }
}
