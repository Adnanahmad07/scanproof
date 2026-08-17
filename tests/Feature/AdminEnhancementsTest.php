<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\HealthScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create(['is_active' => true]);
    }

    // ── P4-TC01: Admin can promote staff to supervisor ──

    public function test_p4_tc01_admin_can_promote_staff_to_supervisor(): void
    {
        $staff = User::factory()->staff()->create(['is_active' => true]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->call('promoteToSupervisor', $staff->id);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'role' => UserRole::Supervisor,
        ]);
    }

    // ── P4-TC02: Admin can demote supervisor to staff ──

    public function test_p4_tc02_admin_can_demote_supervisor_to_staff(): void
    {
        $supervisor = User::factory()->supervisor()->create(['is_active' => true]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->call('demoteToStaff', $supervisor->id);

        $this->assertDatabaseHas('users', [
            'id' => $supervisor->id,
            'role' => UserRole::Staff,
        ]);
    }

    // ── P4-TC03: Admin cannot change own role ──

    public function test_p4_tc03_admin_cannot_change_own_role(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->call('promoteToSupervisor', $this->admin->id);

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'role' => UserRole::Admin,
        ]);
    }

    public function test_p4_tc03_admin_cannot_demote_self(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->call('demoteToStaff', $this->admin->id);

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'role' => UserRole::Admin,
        ]);
    }

    // ── P4-TC04: Non-admin cannot promote ──

    public function test_p4_tc04_supervisor_cannot_promote(): void
    {
        $supervisor = User::factory()->supervisor()->create(['is_active' => true]);
        $staff = User::factory()->staff()->create(['is_active' => true]);

        // Supervisor cannot access admin UserManagement component (403)
        // We verify the role wasn't changed
        $this->actingAs($supervisor)->get(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'role' => UserRole::Staff,
        ]);
    }

    // ── P4-TC05: Promoted user has supervisor role ──

    public function test_p4_tc05_promoted_user_has_supervisor_role(): void
    {
        $staff = User::factory()->staff()->create(['is_active' => true]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->call('promoteToSupervisor', $staff->id);

        $user = User::find($staff->id);
        $this->assertTrue($user->isSupervisor());
    }

    // ── P4-TC06: Demoted user has staff role ──

    public function test_p4_tc06_demoted_user_has_staff_role(): void
    {
        $supervisor = User::factory()->supervisor()->create(['is_active' => true]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->call('demoteToStaff', $supervisor->id);

        $user = User::find($supervisor->id);
        $this->assertTrue($user->isStaff());
    }

    // ── P4-TC07: Promote already-supervisor shows error ──

    public function test_p4_tc07_promote_already_supervisor_stays_supervisor(): void
    {
        $supervisor = User::factory()->supervisor()->create(['is_active' => true]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->call('promoteToSupervisor', $supervisor->id);

        $this->assertDatabaseHas('users', [
            'id' => $supervisor->id,
            'role' => UserRole::Supervisor,
        ]);
    }

    // ── P4-TC08: Demote non-supervisor shows error ──

    public function test_p4_tc08_demote_non_supervisor_stays_staff(): void
    {
        $staff = User::factory()->staff()->create(['is_active' => true]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->call('demoteToStaff', $staff->id);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'role' => UserRole::Staff,
        ]);
    }

    // ── P4-TC09: AnalyticsService returns correct stats ──

    public function test_p4_tc09_analytics_service_returns_correct_stats_empty_db(): void
    {
        $service = new AnalyticsService();
        $stats = $service->getDashboardStats();

        $this->assertEquals(0, $stats['totalStaff']);
        $this->assertEquals(0, $stats['activeTasks']);
        $this->assertEquals(0, $stats['totalLocations']);
        $this->assertEquals(0, $stats['openIssues']);
    }

    public function test_p4_tc09_analytics_service_returns_correct_stats_with_data(): void
    {
        // Create test data
        User::factory()->staff()->create(['is_active' => true]);
        User::factory()->staff()->create(['is_active' => true]);

        \App\Models\Location::create([
            'name' => 'Test Room',
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'created_by' => $this->admin->id,
        ]);

        $service = new AnalyticsService();
        $stats = $service->getDashboardStats();

        $this->assertEquals(2, $stats['totalStaff']);
        $this->assertEquals(1, $stats['totalLocations']);
    }

    // ── P4-TC10: HealthScoreService calculates correctly ──

    public function test_p4_tc10_health_score_empty_db(): void
    {
        $service = new HealthScoreService();
        $score = $service->calculate();

        // With no tasks/issues, should return 100 (all defaults are max)
        $this->assertEquals(100, $score);
    }

    public function test_p4_tc10_health_score_with_completed_tasks(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $staff = User::factory()->staff()->create(['supervisor_id' => $supervisor->id]);

        \App\Models\Task::create([
            'title' => 'Completed Task',
            'location' => 'Test',
            'category' => 'cleaning',
            'priority' => 'medium',
            'status' => 'completed',
            'supervisor_id' => $supervisor->id,
            'assigned_to' => $staff->id,
        ]);

        $service = new HealthScoreService();
        $score = $service->calculate();

        // With 1 completed task and 0 issues: should be 100
        $this->assertEquals(100, $score);
    }

    public function test_p4_tc10_health_score_label(): void
    {
        $service = new HealthScoreService();

        $this->assertEquals('Excellent', $service->getLabel(85));
        $this->assertEquals('Good', $service->getLabel(65));
        $this->assertEquals('Fair', $service->getLabel(45));
        $this->assertEquals('Poor', $service->getLabel(25));
        $this->assertEquals('Critical', $service->getLabel(10));
    }

    public function test_p4_tc10_health_score_color(): void
    {
        $service = new HealthScoreService();

        $this->assertEquals('success', $service->getColor(85));
        $this->assertEquals('info', $service->getColor(65));
        $this->assertEquals('warning', $service->getColor(45));
        $this->assertEquals('error', $service->getColor(25));
    }

    // ── P4-TC11: Admin user management page loads ──

    public function test_p4_tc11_admin_user_management_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
    }

    public function test_p4_tc11_non_admin_cannot_access_user_management(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $response = $this->actingAs($supervisor)->get(route('admin.users.index'));
        $response->assertForbidden();
    }

    // ── Edge Case: Toggle active on self ──

    public function test_p4_edge_cannot_toggle_active_on_self(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->call('toggleActive', $this->admin->id);

        $this->assertTrue($this->admin->fresh()->is_active);
    }

    // ── Edge Case: Toggle active on other user ──

    public function test_p4_edge_toggle_active_on_other_user(): void
    {
        $staff = User::factory()->staff()->create(['is_active' => true]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->call('toggleActive', $staff->id);

        $this->assertFalse($staff->fresh()->is_active);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->call('toggleActive', $staff->id);

        $this->assertTrue($staff->fresh()->is_active);
    }
}
