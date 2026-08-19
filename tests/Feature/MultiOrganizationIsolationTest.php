<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Enums\UserRole;
use App\Livewire\Admin\LocationManagement;
use App\Livewire\Admin\SupervisorManagement;
use App\Models\Issue;
use App\Models\Location;
use App\Models\Organization;
use App\Models\SupervisorInvitation;
use App\Models\User;
use App\Services\LocationImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Multi-organization isolation tests.
 *
 * Verifies that two different organizations registered on the platform
 * cannot see, modify, or act upon each other's data (supervisors,
 * locations, issues), and that the anonymous QR issue-reporting flow
 * routes issues into the correct organization.
 */
class MultiOrganizationIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $orgA;
    protected Organization $orgB;
    protected User $adminA;
    protected User $adminB;

    protected string $password = 'StrongPass123!';

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgA = Organization::create(['name' => 'Alpha Facility', 'slug' => 'alpha-facility']);
        $this->orgB = Organization::create(['name' => 'Beta Facility', 'slug' => 'beta-facility']);

        $this->adminA = User::factory()->admin()->forOrganization($this->orgA->id)->create([
            'email' => 'adminA@example.com',
            'password' => bcrypt($this->password),
        ]);
        $this->adminB = User::factory()->admin()->forOrganization($this->orgB->id)->create([
            'email' => 'adminB@example.com',
            'password' => bcrypt($this->password),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // A. REGISTRATION FLOW
    // ═══════════════════════════════════════════════════════════════

    public function test_tc_iso_1_register_org_a_creates_org_and_redirects_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Org A Owner',
            'email' => 'owner-a@example.com',
            'organization_name' => 'Alpha Facility',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertAuthenticated();

        $user = User::where('email', 'owner-a@example.com')->firstOrFail();
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertNotNull($user->email_verified_at, 'Registered user must be verified (no SMTP dependency).');
        $this->assertNotNull($user->organization_id);

        $org = $user->organization;
        $this->assertSame('Alpha Facility', $org->name);
        $this->assertSame('alpha-facility-1', $org->slug);

        $response = $this->get('/dashboard');
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_tc_iso_2_register_org_b_gets_separate_organization(): void
    {
        $this->post('/register', [
            'name' => 'Org B Owner',
            'email' => 'owner-b@example.com',
            'organization_name' => 'Beta Facility',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ])->assertRedirect('/dashboard');

        $this->post('/logout');

        $this->post('/register', [
            'name' => 'Gamma Owner',
            'email' => 'owner-gamma@example.com',
            'organization_name' => 'Gamma Facility',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ])->assertRedirect('/dashboard');

        $first = User::where('email', 'owner-b@example.com')->firstOrFail();
        $second = User::where('email', 'owner-gamma@example.com')->firstOrFail();

        $this->assertNotSame($first->organization_id, $second->organization_id);
        $this->assertSame(4, Organization::count());
        $this->assertNotSame($this->orgA->id, $first->organization_id);
        $this->assertNotSame($this->orgB->id, $first->organization_id);
        $this->assertNotSame($this->orgA->id, $second->organization_id);
        $this->assertNotSame($this->orgB->id, $second->organization_id);
    }

    public function test_tc_iso_3_register_same_org_name_gets_unique_slug(): void
    {
        $this->post('/register', [
            'name' => 'Owner One',
            'email' => 'one@example.com',
            'organization_name' => 'Same Name Org',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ])->assertRedirect('/dashboard');

        $this->post('/logout');

        $this->post('/register', [
            'name' => 'Owner Two',
            'email' => 'two@example.com',
            'organization_name' => 'Same Name Org',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ])->assertRedirect('/dashboard');

        $slugs = Organization::pluck('slug');
        $this->assertCount(4, $slugs);
        $this->assertSame(4, $slugs->unique()->count());
        $this->assertTrue($slugs->contains('same-name-org'));
        $this->assertTrue($slugs->contains('same-name-org-1'));
    }

    public function test_tc_iso_4_register_rejects_duplicate_email(): void
    {
        $this->post('/register', [
            'name' => 'Dup User',
            'email' => 'dup@example.com',
            'organization_name' => 'Dup Org',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ])->assertRedirect('/dashboard');

        $this->post('/logout');

        $this->post('/register', [
            'name' => 'Dup User 2',
            'email' => 'dup@example.com',
            'organization_name' => 'Other Org',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'dup@example.com')->count());
    }

    public function test_tc_iso_5_register_rejects_invalid_input(): void
    {
        $this->post('/register', [
            'name' => '',
            'email' => 'not-an-email',
            'organization_name' => '',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ])->assertSessionHasErrors(['name', 'email', 'organization_name', 'password']);

        $this->assertSame(0, User::where('email', 'not-an-email')->count());
    }

    public function test_tc_iso_6_register_logs_in_immediately(): void
    {
        $this->post('/register', [
            'name' => 'Instant User',
            'email' => 'instant@example.com',
            'organization_name' => 'Instant Org',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ]);

        $this->assertAuthenticated();

        $this->get('/admin/dashboard')->assertOk();
    }

    // ═══════════════════════════════════════════════════════════════
    // B. LOGIN & DASHBOARD REDIRECT
    // ═══════════════════════════════════════════════════════════════

    public function test_tc_iso_7_each_org_admin_lands_on_own_dashboard_after_login(): void
    {
        $this->post('/login', [
            'email' => 'adminA@example.com',
            'password' => $this->password,
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertRedirect('/admin/dashboard');

        $this->post('/logout');

        $this->post('/login', [
            'email' => 'adminB@example.com',
            'password' => $this->password,
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertRedirect('/admin/dashboard');
    }

    public function test_tc_iso_8_dashboard_shows_own_organization_name(): void
    {
        $response = $this->actingAs($this->adminA)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Alpha Facility');
        $response->assertDontSee('Beta Facility');
    }

    // ═══════════════════════════════════════════════════════════════
    // C. SUPERVISOR FLOW & INVITATION ISOLATION
    // ═══════════════════════════════════════════════════════════════

    public function test_tc_iso_10_admin_invites_supervisor_into_own_org(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->adminA)->post(route('admin.supervisors.invite'), [
            'email' => 'sup-a@example.com',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('supervisor_invitations', [
            'email' => 'sup-a@example.com',
            'organization_id' => $this->orgA->id,
            'status' => 'pending',
        ]);

        Mail::assertSent(\App\Mail\SupervisorInvitationMail::class);
    }

    public function test_tc_iso_11_same_email_can_be_invited_in_both_orgs(): void
    {
        Mail::fake();

        $this->actingAs($this->adminA)->post(route('admin.supervisors.invite'), [
            'email' => 'shared@example.com',
        ])->assertRedirect();

        $this->actingAs($this->adminB)->post(route('admin.supervisors.invite'), [
            'email' => 'shared@example.com',
        ])->assertRedirect();

        $this->assertSame(2, SupervisorInvitation::where('email', 'shared@example.com')->count());
        $this->assertSame(1, SupervisorInvitation::where('email', 'shared@example.com')->where('organization_id', $this->orgA->id)->count());
        $this->assertSame(1, SupervisorInvitation::where('email', 'shared@example.com')->where('organization_id', $this->orgB->id)->count());
    }

    public function test_tc_iso_12_supervisor_set_password_joins_correct_org(): void
    {
        $invitation = SupervisorInvitation::create([
            'email' => 'supervisor-a@example.com',
            'token' => 'token-org-a-1234567890',
            'invited_by' => $this->adminA->id,
            'organization_id' => $this->orgA->id,
            'expires_at' => now()->addHours(48),
            'status' => 'pending',
        ]);

        $response = $this->get(route('supervisor.set-password', $invitation->token));
        $response->assertOk();

        Livewire::test(\App\Livewire\Supervisor\SetPassword::class, ['token' => $invitation->token])
            ->set('password', $this->password)
            ->set('password_confirmation', $this->password)
            ->call('setPassword')
            ->assertRedirect(UserRole::Supervisor->dashboardPath());

        $supervisor = User::where('email', 'supervisor-a@example.com')->firstOrFail();
        $this->assertSame(UserRole::Supervisor, $supervisor->role);
        $this->assertSame($this->orgA->id, $supervisor->organization_id);

        $invitation->refresh();
        $this->assertTrue($invitation->isAccepted());
    }

    public function test_tc_iso_13_expired_invitation_is_rejected(): void
    {
        $invitation = SupervisorInvitation::create([
            'email' => 'late-supervisor@example.com',
            'token' => 'expired-token-1234567890',
            'invited_by' => $this->adminA->id,
            'organization_id' => $this->orgA->id,
            'expires_at' => now()->subHours(1),
            'status' => 'pending',
        ]);

        $this->get(route('supervisor.set-password', $invitation->token))
            ->assertStatus(410);

        $this->assertSame(0, User::where('email', 'late-supervisor@example.com')->count());
    }

    public function test_tc_iso_14_admin_cannot_cancel_other_orgs_invitation_via_route(): void
    {
        $invitationB = SupervisorInvitation::create([
            'email' => 'sup-b@example.com',
            'token' => 'token-org-b-1234567890',
            'invited_by' => $this->adminB->id,
            'organization_id' => $this->orgB->id,
            'expires_at' => now()->addHours(48),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminA)
            ->delete(route('admin.supervisors.cancel', $invitationB));

        $response->assertForbidden();
        $this->assertDatabaseHas('supervisor_invitations', [
            'id' => $invitationB->id,
            'status' => 'pending',
        ]);
    }

    public function test_tc_iso_15_admin_cannot_cancel_other_orgs_invitation_via_livewire(): void
    {
        $invitationB = SupervisorInvitation::create([
            'email' => 'sup-b@example.com',
            'token' => 'token-org-b-2234567890',
            'invited_by' => $this->adminB->id,
            'organization_id' => $this->orgB->id,
            'expires_at' => now()->addHours(48),
            'status' => 'pending',
        ]);

        Livewire::actingAs($this->adminA)
            ->test(SupervisorManagement::class)
            ->call('cancelInvitation', $invitationB->id)
            ->assertStatus(404);

        $this->assertDatabaseHas('supervisor_invitations', [
            'id' => $invitationB->id,
            'status' => 'pending',
        ]);
    }

    public function test_tc_iso_16_admin_cannot_demote_other_orgs_supervisor_via_livewire(): void
    {
        $supervisorB = User::factory()->supervisor()->forOrganization($this->orgB->id)->create([
            'email' => 'sup-b@example.com',
        ]);

        Livewire::actingAs($this->adminA)
            ->test(SupervisorManagement::class)
            ->set('supervisorToDelete', $supervisorB->id)
            ->call('deleteSupervisor')
            ->assertStatus(404);

        $supervisorB->refresh();
        $this->assertSame(UserRole::Supervisor, $supervisorB->role);
    }

    // ═══════════════════════════════════════════════════════════════
    // D. LOCATION ISOLATION
    // ═══════════════════════════════════════════════════════════════

    public function test_tc_iso_20_admin_creates_location_in_own_org(): void
    {
        Livewire::actingAs($this->adminA)
            ->test(LocationManagement::class)
            ->set('name', 'Tower A')
            ->set('type', 'room')
            ->set('building', 'Main')
            ->call('createLocation');

        $this->assertDatabaseHas('locations', [
            'name' => 'Tower A',
            'organization_id' => $this->orgA->id,
        ]);
    }

    public function test_tc_iso_21_location_lists_are_org_scoped(): void
    {
        $locA = Location::create([
            'name' => 'Location A1',
            'type' => LocationType::Room,
            'organization_id' => $this->orgA->id,
            'created_by' => $this->adminA->id,
        ]);
        Location::create([
            'name' => 'Location B1',
            'type' => LocationType::Room,
            'organization_id' => $this->orgB->id,
            'created_by' => $this->adminB->id,
        ]);

        $component = Livewire::actingAs($this->adminA)
            ->test(LocationManagement::class);

        $locations = $component->viewData('locations');
        $this->assertCount(1, $locations);
        $this->assertSame($locA->id, $locations->first()->id);
    }

    public function test_tc_iso_22_location_csv_import_scopes_to_importers_org(): void
    {
        $csv = "name,type,building,floor\n"
            . "Imported Room A,room,Main,1\n";

        $importer = new LocationImporter();
        $result = $importer->import($csv, $this->adminA);

        $this->assertEquals(1, $result['created']);

        $location = Location::where('name', 'Imported Room A')->firstOrFail();
        $this->assertSame($this->orgA->id, $location->organization_id);
    }

    public function test_tc_iso_23_same_location_name_allowed_in_different_orgs(): void
    {
        Location::create([
            'name' => 'Room 101',
            'type' => LocationType::Room,
            'organization_id' => $this->orgA->id,
            'created_by' => $this->adminA->id,
        ]);

        $csv = "name,type\n"
            . "Room 101,room\n";

        $importer = new LocationImporter();
        $result = $importer->import($csv, $this->adminB);

        $this->assertEquals(1, $result['created']);
        $this->assertSame(
            2,
            Location::where('name', 'Room 101')->count()
        );
    }

    public function test_tc_iso_24_admin_cannot_edit_other_orgs_location_via_livewire(): void
    {
        $locB = Location::create([
            'name' => 'Secret Room B',
            'type' => LocationType::Room,
            'organization_id' => $this->orgB->id,
            'created_by' => $this->adminB->id,
        ]);

        Livewire::actingAs($this->adminA)
            ->test(LocationManagement::class)
            ->call('openEditModal', $locB->id)
            ->assertStatus(404);
    }

    public function test_tc_iso_25_admin_cannot_delete_other_orgs_location_via_livewire(): void
    {
        $locB = Location::create([
            'name' => 'Secret Room B2',
            'type' => LocationType::Room,
            'organization_id' => $this->orgB->id,
            'created_by' => $this->adminB->id,
        ]);

        Livewire::actingAs($this->adminA)
            ->test(LocationManagement::class)
            ->set('deleteId', $locB->id)
            ->call('deleteLocation')
            ->assertStatus(404);

        $this->assertDatabaseHas('locations', ['id' => $locB->id]);
    }

    // ═══════════════════════════════════════════════════════════════
    // E. ANONYMOUS ISSUE ISOLATION (QR REPORT FLOW)
    // ═══════════════════════════════════════════════════════════════

    public function test_tc_iso_30_anonymous_issue_lands_in_locations_org(): void
    {
        $locA = Location::create([
            'name' => 'Public Lobby A',
            'type' => LocationType::Lobby,
            'organization_id' => $this->orgA->id,
            'created_by' => $this->adminA->id,
        ]);
        $locB = Location::create([
            'name' => 'Public Lobby B',
            'type' => LocationType::Lobby,
            'organization_id' => $this->orgB->id,
            'created_by' => $this->adminB->id,
        ]);

        $this->post(route('scan.report.submit', $locA->uuid), [
            'description' => 'Broken light in lobby A',
        ])->assertRedirect();

        $this->post(route('scan.report.submit', $locB->uuid), [
            'description' => 'Broken door in lobby B',
        ])->assertRedirect();

        $issueA = Issue::where('location_id', $locA->id)->firstOrFail();
        $issueB = Issue::where('location_id', $locB->id)->firstOrFail();

        $this->assertSame($this->orgA->id, $issueA->organization_id);
        $this->assertSame($this->orgB->id, $issueB->organization_id);
        $this->assertNotSame($issueA->organization_id, $issueB->organization_id);
    }

    public function test_tc_iso_31_admin_issue_list_excludes_other_orgs_issues(): void
    {
        $locA = Location::create([
            'name' => 'Room A',
            'type' => LocationType::Room,
            'organization_id' => $this->orgA->id,
            'created_by' => $this->adminA->id,
        ]);
        $locB = Location::create([
            'name' => 'Room B',
            'type' => LocationType::Room,
            'organization_id' => $this->orgB->id,
            'created_by' => $this->adminB->id,
        ]);

        Issue::create([
            'location_id' => $locA->id,
            'description' => 'Issue from org A',
            'status' => 'reported',
            'organization_id' => $this->orgA->id,
        ]);
        Issue::create([
            'location_id' => $locB->id,
            'description' => 'Issue from org B',
            'status' => 'reported',
            'organization_id' => $this->orgB->id,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.issues.index'));

        $response->assertOk();
        $response->assertSee('Issue from org A');
        $response->assertDontSee('Issue from org B');
    }

    public function test_tc_iso_32_supervisor_cannot_update_other_orgs_issue(): void
    {
        $supervisorA = User::factory()->supervisor()->forOrganization($this->orgA->id)->create([
            'email' => 'sup-a@example.com',
        ]);

        $locB = Location::create([
            'name' => 'Room B',
            'type' => LocationType::Room,
            'organization_id' => $this->orgB->id,
            'created_by' => $this->adminB->id,
        ]);
        $issueB = Issue::create([
            'location_id' => $locB->id,
            'description' => 'Org B issue',
            'status' => 'reported',
            'organization_id' => $this->orgB->id,
        ]);

        $response = $this->actingAs($supervisorA)
            ->post(route('supervisor.issues.status', $issueB->id), [
                'status' => 'assigned',
            ]);

        $response->assertForbidden();

        $issueB->refresh();
        $this->assertSame('reported', $issueB->status);
    }

    public function test_tc_iso_33_supervisor_cannot_assign_other_orgs_issue(): void
    {
        $supervisorA = User::factory()->supervisor()->forOrganization($this->orgA->id)->create([
            'email' => 'sup-a@example.com',
        ]);
        $staffA = User::factory()->staff()->forOrganization($this->orgA->id)->create([
            'email' => 'staff-a@example.com',
        ]);

        $locB = Location::create([
            'name' => 'Room B2',
            'type' => LocationType::Room,
            'organization_id' => $this->orgB->id,
            'created_by' => $this->adminB->id,
        ]);
        $issueB = Issue::create([
            'location_id' => $locB->id,
            'description' => 'Org B issue to assign',
            'status' => 'reported',
            'organization_id' => $this->orgB->id,
        ]);

        $response = $this->actingAs($supervisorA)
            ->post(route('supervisor.issues.assign', $issueB->id), [
                'assigned_to' => $staffA->id,
            ]);

        $response->assertForbidden();

        $issueB->refresh();
        $this->assertSame('reported', $issueB->status);
        $this->assertNull($issueB->assigned_to);
    }

    public function test_tc_iso_34_tracking_code_public_page_shows_only_public_data(): void
    {
        $locA = Location::create([
            'name' => 'Room A',
            'type' => LocationType::Room,
            'organization_id' => $this->orgA->id,
            'created_by' => $this->adminA->id,
        ]);
        $issue = Issue::create([
            'tracking_code' => 'SP-ISO001',
            'location_id' => $locA->id,
            'description' => 'Public timeline issue',
            'status' => 'reported',
            'organization_id' => $this->orgA->id,
        ]);

        $response = $this->get(route('track.show', 'SP-ISO001'));

        $response->assertOk();
        $response->assertSee('SP-ISO001');
        $response->assertDontSee($this->adminA->email);
    }

    // ═══════════════════════════════════════════════════════════════
    // F. EDGE CASES — SPAM / VALIDATION / ROUTING
    // ═══════════════════════════════════════════════════════════════

    public function test_tc_iso_40_issue_report_min_length_enforced(): void
    {
        $locA = Location::create([
            'name' => 'Room A',
            'type' => LocationType::Room,
            'organization_id' => $this->orgA->id,
            'created_by' => $this->adminA->id,
        ]);

        $this->post(route('scan.report.submit', $locA->uuid), [
            'description' => 'Short',
        ])->assertSessionHasErrors('description');

        $this->assertSame(0, Issue::count());
    }

    public function test_tc_iso_41_honeypot_submission_is_silently_accepted(): void
    {
        $locA = Location::create([
            'name' => 'Room A',
            'type' => LocationType::Room,
            'organization_id' => $this->orgA->id,
            'created_by' => $this->adminA->id,
        ]);

        $response = $this->post(route('scan.report.submit', $locA->uuid), [
            'description' => 'Spam submission attempt',
            'website' => 'http://spam.example.com',
        ]);

        $response->assertRedirect();
        $this->assertSame(0, Issue::count(), 'Honeypot submissions must never create issues.');
    }

    public function test_tc_iso_42_invalid_uuid_returns_404(): void
    {
        $this->get(route('scan.show', 'does-not-exist'))->assertNotFound();
        $this->post(route('scan.report.submit', 'does-not-exist'), [
            'description' => 'This description is long enough',
        ])->assertNotFound();
    }

    public function test_tc_iso_43_rate_limit_blocks_excessive_reports(): void
    {
        $locA = Location::create([
            'name' => 'Room A',
            'type' => LocationType::Room,
            'organization_id' => $this->orgA->id,
            'created_by' => $this->adminA->id,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('scan.report.submit', $locA->uuid), [
                'description' => "Report number {$i}",
            ])->assertRedirect();
        }

        $response = $this->post(route('scan.report.submit', $locA->uuid), [
            'description' => 'Report number six',
        ]);

        $response->assertSessionHasErrors('description');
        $this->assertSame(5, Issue::count());
    }

    // ═══════════════════════════════════════════════════════════════
    // G. ROLE ACCESS CONTROL (cross-org + cross-role)
    // ═══════════════════════════════════════════════════════════════

    public function test_tc_iso_50_staff_cannot_access_admin_routes(): void
    {
        $staffA = User::factory()->staff()->forOrganization($this->orgA->id)->create();

        $this->actingAs($staffA)->get('/admin/users')->assertForbidden();
        $this->actingAs($staffA)->get('/admin/locations')->assertForbidden();
        $this->actingAs($staffA)->get('/admin/supervisors')->assertForbidden();
    }

    public function test_tc_iso_51_supervisor_cannot_access_admin_routes(): void
    {
        $supervisorA = User::factory()->supervisor()->forOrganization($this->orgA->id)->create();

        $this->actingAs($supervisorA)->get('/admin/users')->assertForbidden();
        $this->actingAs($supervisorA)->get('/admin/locations')->assertForbidden();
    }

    public function test_tc_iso_52_admin_cannot_access_supervisor_routes(): void
    {
        $this->actingAs($this->adminA)->get('/supervisor/dashboard')->assertForbidden();
        $this->actingAs($this->adminA)->get('/supervisor/tasks')->assertForbidden();
    }

    public function test_tc_iso_53_guest_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/supervisor/dashboard')->assertRedirect('/login');
        $this->get('/staff/dashboard')->assertRedirect('/login');
    }

    public function test_tc_iso_54_inactive_user_cannot_login(): void
    {
        User::factory()->admin()->forOrganization($this->orgA->id)->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt($this->password),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => $this->password,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    public function test_tc_iso_55_admin_cannot_update_other_orgs_user(): void
    {
        $staffB = User::factory()->staff()->forOrganization($this->orgB->id)->create([
            'email' => 'staff-b@example.com',
        ]);

        $response = $this->actingAs($this->adminA)
            ->post(route('admin.users.update', $staffB->id), [
                'editName' => 'Hacked Name',
                'editEmail' => 'staff-b@example.com',
                'editRole' => 'staff',
                'editIsActive' => true,
            ]);

        $response->assertForbidden();

        $staffB->refresh();
        $this->assertNotSame('Hacked Name', $staffB->name);
    }

    public function test_tc_iso_56_admin_cannot_toggle_other_orgs_user(): void
    {
        $staffB = User::factory()->staff()->forOrganization($this->orgB->id)->create([
            'email' => 'staff-b2@example.com',
        ]);

        $response = $this->actingAs($this->adminA)
            ->post(route('admin.users.toggle-active', $staffB->id));

        $response->assertForbidden();

        $staffB->refresh();
        $this->assertTrue($staffB->is_active);
    }
}
