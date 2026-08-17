<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupervisorDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;
    protected User $staff;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = User::factory()->supervisor()->create();
        $this->staff = User::factory()->staff()->create([
            'supervisor_id' => $this->supervisor->id,
        ]);

        $this->location = Location::create([
            'name' => 'Test Room',
            'type' => LocationType::Room,
            'building' => 'Test Building',
            'floor' => 'Floor 1',
            'configured' => true,
            'configured_by' => $this->supervisor->id,
            'configured_at' => now(),
            'created_by' => $this->supervisor->id,
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    // ── DASHBOARD ──

    public function test_supervisor_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.dashboard'));

        $response->assertStatus(200);
    }

    public function test_dashboard_shows_real_stats(): void
    {
        Task::create([
            'title' => 'Test Task',
            'location' => 'Room 101',
            'status' => 'pending',
            'supervisor_id' => $this->supervisor->id,
            'assigned_to' => $this->staff->id,
        ]);

        Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Test issue',
            'status' => 'reported',
        ]);

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\Dashboard::class)
            ->assertSee('1')
            ->assertSee('Total Tasks')
            ->assertSee('Open Issues');
    }

    public function test_non_supervisor_cannot_access_dashboard(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)
            ->get(route('supervisor.dashboard'));

        $response->assertForbidden();
    }

    // ── SCAN QR ──

    public function test_supervisor_can_access_scan_qr(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.scan-qr'));

        $response->assertStatus(200);
    }

    public function test_non_supervisor_cannot_access_scan_qr(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)
            ->get(route('supervisor.scan-qr'));

        $response->assertForbidden();
    }

    // ── SIDEBAR LINKS ──

    public function test_supervisor_sidebar_has_scan_qr_link(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.dashboard'));

        $response->assertSee(route('supervisor.scan-qr'));
        $response->assertSee('Scan QR');
    }
}
