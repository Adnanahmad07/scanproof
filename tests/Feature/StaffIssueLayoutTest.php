<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Models\Issue;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffIssueLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $supervisor;
    protected User $staff;
    protected User $otherStaff;
    protected Location $location;
    protected Issue $issue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->supervisor = User::factory()->supervisor()->create();
        $this->staff = User::factory()->staff()->create([
            'supervisor_id' => $this->supervisor->id,
        ]);
        $this->otherStaff = User::factory()->staff()->create([
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
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);
    }

    // TC-P1.1: Staff can access issues page via sidebar
    public function test_staff_can_access_issues_page(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('staff.issues.index'));

        $response->assertStatus(200);
    }

    // TC-P1.2: Unauthenticated user cannot access staff issues
    public function test_unauthenticated_user_redirected_from_staff_issues(): void
    {
        $response = $this->get(route('staff.issues.index'));

        $response->assertRedirect(route('login'));
    }

    // TC-P1.3: Non-staff user cannot access staff issues
    public function test_supervisor_cannot_access_staff_issues(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get(route('staff.issues.index'));

        $response->assertForbidden();
    }

    public function test_admin_cannot_access_staff_issues(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('staff.issues.index'));

        $response->assertForbidden();
    }

    // TC-P1.4: Issues page extends staff layout (contains sidebar)
    public function test_staff_issues_page_contains_staff_layout_sidebar(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('staff.issues.index'));

        $response->assertSee('/staff/dashboard');
        $response->assertSee('/staff/tasks');
        $response->assertSee('/staff/issues');
        $response->assertSee('Scan QR');
        $response->assertSee('My Issues');
    }

    // TC-P1.5: Issues page shows assigned issues
    public function test_staff_issues_page_shows_assigned_issues(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('staff.issues.index'));

        $response->assertSee($this->issue->tracking_code);
        $response->assertSee('The light bulb is broken');
        $response->assertSee('Test Room');
    }

    // TC-P1.6: Staff issues page renders with Vite CSS
    public function test_staff_issues_page_has_vite_assets(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('staff.issues.index'));

        $response->assertSee('build/assets/app-');
    }

    // TC-P1.7: Staff issue show page works with layout
    public function test_staff_can_view_own_issue_detail(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('staff.issues.show', $this->issue->id));

        $response->assertStatus(200);
        $response->assertSee($this->issue->tracking_code);
        $response->assertSee('The light bulb is broken');
        $response->assertSee('/staff/dashboard');
    }

    // TC-P1.8: Staff issue show page blocks other staff issues
    public function test_staff_cannot_view_other_staff_issue(): void
    {
        $otherIssue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Issue assigned to other staff member',
            'status' => 'assigned',
            'assigned_to' => $this->otherStaff->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.issues.show', $otherIssue->id));

        $response->assertForbidden();
    }

    // Extra: Staff only sees own issues in list
    public function test_staff_only_sees_own_issues_in_list(): void
    {
        $otherIssue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Issue assigned to other staff member',
            'status' => 'assigned',
            'assigned_to' => $this->otherStaff->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.issues.index'));

        $response->assertSee($this->issue->tracking_code);
        $response->assertDontSee('Issue assigned to other staff member');
    }

    // Extra: Empty state when no issues assigned
    public function test_staff_issues_page_shows_empty_state(): void
    {
        $noIssuesStaff = User::factory()->staff()->create([
            'supervisor_id' => $this->supervisor->id,
        ]);

        $response = $this->actingAs($noIssuesStaff)
            ->get(route('staff.issues.index'));

        $response->assertSee('No issues assigned');
    }
}
