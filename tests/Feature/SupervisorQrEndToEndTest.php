<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Models\Issue;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupervisorQrEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $supervisor;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->supervisor = User::factory()->supervisor()->canPrint()->create();
        $this->staff = User::factory()->staff()->create([
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    /** ── COMPLETE QR LIFECYCLE: Admin creates → Supervisor configures → Public scans → Reports issue → Supervisor assigns → Staff resolves ── */

    public function test_complete_qr_lifecycle_from_creation_to_resolution(): void
    {
        // Step 1: Admin creates a location hierarchy via the admin location management
        $building = Location::create([
            'name' => 'HQ Tower',
            'type' => LocationType::Building,
            'building' => 'HQ Tower',
            'created_by' => $this->admin->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $floor = Location::create([
            'name' => 'Ground Floor',
            'type' => LocationType::Floor,
            'parent_id' => $building->id,
            'building' => 'HQ Tower',
            'floor' => 'Ground Floor',
            'created_by' => $this->admin->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $room = Location::create([
            'name' => 'Reception',
            'type' => LocationType::Room,
            'parent_id' => $floor->id,
            'building' => 'HQ Tower',
            'floor' => 'Ground Floor',
            'created_by' => $this->admin->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        // Step 2: Location is auto-configured on creation (has a name)
        $this->assertTrue($room->configured);
        $this->assertNotEmpty($room->uuid);
        $this->assertStringStartsWith(url('/r/'), $room->scanUrl());

        // Step 3: Anonymous user scans the QR → sees location info
        $response = $this->get(route('scan.show', $room->uuid));
        $response->assertStatus(200);
        $response->assertSee('Reception');
        $response->assertSee('Room');
        $response->assertSee('Report Issue');

        // Step 7: Anonymous user reports an issue
        $response = $this->post(route('scan.report.submit', $room->uuid), [
            'description' => 'The reception desk has a broken drawer that needs fixing',
        ]);
        $response->assertRedirect();

        $issue = Issue::where('location_id', $room->id)->first();
        $this->assertNotNull($issue);
        $this->assertStringStartsWith('SP-', $issue->tracking_code);
        $this->assertEquals('reported', $issue->status);

        // Step 8: Public user can track the issue
        $response = $this->get(route('track.show', $issue->tracking_code));
        $response->assertStatus(200);
        $response->assertSee('Reported');

        // Step 9: Supervisor sees the issue in their dashboard
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.issues.index'));
        $response->assertStatus(200);
        $response->assertSee('Reception');
        $response->assertSee('broken drawer');

        // Step 10: Supervisor assigns the issue to staff
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.assign', $issue->id), [
                'assigned_to' => $this->staff->id,
            ]);
        $response->assertRedirect();

        $issue->refresh();
        $this->assertEquals('assigned', $issue->status);
        $this->assertEquals($this->staff->id, $issue->assigned_to);

        // Step 11: Staff sees the assigned issue
        $response = $this->actingAs($this->staff)
            ->get(route('staff.issues.index'));
        $response->assertStatus(200);
        $response->assertSee('Reception');

        // Step 12: Staff starts working on the issue
        $response = $this->actingAs($this->staff)
            ->post(route('staff.issues.status', $issue->id), [
                'status' => 'in_progress',
            ]);
        $response->assertRedirect();

        $issue->refresh();
        $this->assertEquals('in_progress', $issue->status);

        // Step 13: Staff completes the issue
        $response = $this->actingAs($this->staff)
            ->post(route('staff.issues.status', $issue->id), [
                'status' => 'resolved',
            ]);
        $response->assertRedirect();

        $issue->refresh();
        $this->assertEquals('resolved', $issue->status);
        $this->assertNotNull($issue->resolved_at);

        // Step 14: Issue events audit trail is recorded
        $this->assertDatabaseHas('issue_events', [
            'issue_id' => $issue->id,
            'from_status' => 'reported',
            'to_status' => 'assigned',
            'actor_id' => $this->supervisor->id,
        ]);
        $this->assertDatabaseHas('issue_events', [
            'issue_id' => $issue->id,
            'from_status' => 'assigned',
            'to_status' => 'in_progress',
            'actor_id' => $this->staff->id,
        ]);
        $this->assertDatabaseHas('issue_events', [
            'issue_id' => $issue->id,
            'from_status' => 'in_progress',
            'to_status' => 'resolved',
            'actor_id' => $this->staff->id,
        ]);
    }

    /** ── QR CODE GENERATION ── */

    public function test_qr_codes_encode_correct_scan_urls(): void
    {
        $location = Location::create([
            'name' => 'Meeting Room',
            'type' => LocationType::Room,
            'building' => 'HQ Tower',
            'created_by' => $this->supervisor->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $url = $location->scanUrl();
        $this->assertEquals(url('/r/' . $location->uuid), $url);

        // QR code SVG contains the URL
        $qr = app(\App\Services\QrCodeService::class);
        $svg = $qr->inlineSvg($url);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertNotEmpty($svg);
    }

    public function test_uuid_is_always_used_in_scan_url_never_numeric_id(): void
    {
        $location = Location::create([
            'name' => 'Secure Area',
            'type' => LocationType::Room,
            'building' => 'HQ Tower',
            'created_by' => $this->supervisor->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $url = $location->scanUrl();
        // URL uses /r/{uuid}, never /r/{numeric_id}
        $this->assertStringStartsWith(url('/r/'), $url);
        $this->assertStringContainsString($location->uuid, $url);
        // Verify the UUID in the URL matches the location's UUID, not a numeric ID
        $path = parse_url($url, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        $this->assertEquals($location->uuid, $segments[1] ?? null, 'Scan URL should use UUID, not numeric ID');
    }

    /** ── ACCESS CONTROL ── */

    public function test_scan_shows_room_info_for_any_location(): void
    {
        $location = Location::create([
            'name' => 'Test Location',
            'type' => LocationType::Room,
            'building' => 'Test Building',
            'created_by' => $this->admin->id,
        ]);

        // Staff sees room info (not "Not Configured")
        $response = $this->actingAs($this->staff)
            ->get(route('scan.show', $location->uuid));
        $response->assertStatus(200);
        $response->assertSee('Test Location');
        $response->assertSee('Report Issue');

        // Anonymous sees room info
        $response = $this->get(route('scan.show', $location->uuid));
        $response->assertStatus(200);
        $response->assertSee('Test Location');
        $response->assertSee('Report Issue');
    }

    /** ── LOCATION CREATION + QR FLOW ── */

    public function test_supervisor_can_create_child_location_and_configure_qr(): void
    {
        $building = Location::create([
            'name' => 'Campus',
            'type' => LocationType::Building,
            'building' => 'Campus',
            'created_by' => $this->supervisor->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        // Create a floor via Livewire
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\LocationQrManagement::class)
            ->call('openCreateModal', $building->id)
            ->set('newName', 'Floor 3')
            ->set('newType', 'room')
            ->call('createLocation');

        $child = Location::where('parent_id', $building->id)
            ->where('name', 'Floor 3')
            ->first();

        $this->assertNotNull($child);
        $this->assertNotEmpty($child->uuid);
        $this->assertTrue($child->configured);

        // Verify QR URL works
        $response = $this->get(route('scan.show', $child->uuid));
        $response->assertStatus(200);
        $response->assertSee('Floor 3');
        $response->assertSee('Report Issue');
    }

    /** ── SCOPING ── */

    public function test_supervisor_only_sees_own_locations_in_qr_management(): void
    {
        $otherSupervisor = User::factory()->supervisor()->create();

        $myLocation = Location::create([
            'name' => 'My Lab',
            'type' => LocationType::Room,
            'building' => 'My Building',
            'created_by' => $this->supervisor->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $otherLocation = Location::create([
            'name' => 'Other Lab',
            'type' => LocationType::Room,
            'building' => 'Other Building',
            'created_by' => $otherSupervisor->id,
            'supervisor_id' => $otherSupervisor->id,
        ]);

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\LocationQrManagement::class)
            ->assertSee('My Lab')
            ->assertDontSee('Other Lab');
    }

    /** ── ISSUE REPORT ON CONFIGURED LOCATION ── */

    public function test_anonymous_can_report_issue_on_configured_location(): void
    {
        $location = Location::create([
            'name' => 'Bathroom A',
            'type' => LocationType::Bathroom,
            'building' => 'Main Building',
            'configured' => true,
            'configured_by' => $this->supervisor->id,
            'configured_at' => now(),
            'created_by' => $this->supervisor->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        // GET the report form
        $response = $this->get(route('scan.report', $location->uuid));
        $response->assertStatus(200);
        $response->assertSee('Report Issue');
        $response->assertSee('Bathroom A');

        // POST the issue
        $response = $this->post(route('scan.report.submit', $location->uuid), [
            'description' => 'The bathroom faucet is leaking water onto the floor',
        ]);
        $response->assertRedirect();

        $issue = Issue::where('location_id', $location->id)->first();
        $this->assertNotNull($issue);
        $this->assertEquals('reported', $issue->status);
        $this->assertEquals('The bathroom faucet is leaking water onto the floor', $issue->description);
        $this->assertStringStartsWith('SP-', $issue->tracking_code);
    }

    public function test_cannot_report_issue_with_short_description(): void
    {
        $location = Location::create([
            'name' => 'Office B',
            'type' => LocationType::Office,
            'building' => 'Main Building',
            'configured' => true,
            'configured_by' => $this->supervisor->id,
            'configured_at' => now(),
            'created_by' => $this->supervisor->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $response = $this->post(route('scan.report.submit', $location->uuid), [
            'description' => 'Short',
        ]);

        $response->assertSessionHasErrors('description');
    }

    /** ── ISSUE CONVERT TO TASK ── */

    public function test_supervisor_can_convert_issue_to_task(): void
    {
        $location = Location::create([
            'name' => 'Kitchen',
            'type' => LocationType::Kitchen,
            'building' => 'Main Building',
            'configured' => true,
            'configured_by' => $this->supervisor->id,
            'configured_at' => now(),
            'created_by' => $this->supervisor->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $issue = Issue::create([
            'location_id' => $location->id,
            'description' => 'Kitchen stove not heating properly',
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $issue->id), [
                'category' => 'maintenance',
                'priority' => 'high',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'supervisor_id' => $this->supervisor->id,
            'assigned_to' => $this->staff->id,
            'location_id' => $location->id,
            'issue_id' => $issue->id,
        ]);
    }

}
