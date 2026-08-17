<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupervisorQrMappingTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;
    protected User $otherSupervisor;
    protected User $admin;
    protected User $staff;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->supervisor = User::factory()->supervisor()->create();
        $this->otherSupervisor = User::factory()->supervisor()->create();
        $this->staff = User::factory()->staff()->create();

        $this->location = Location::create([
            'name' => 'Main Building',
            'type' => LocationType::Building,
            'building' => 'Main Building',
            'created_by' => $this->admin->id,
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    public function test_supervisor_can_access_scan_qr_page(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('supervisor.scan-qr'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_scan_qr(): void
    {
        $response = $this->get(route('supervisor.scan-qr'));
        $response->assertRedirect('/login');
    }

    public function test_admin_cannot_access_scan_qr(): void
    {
        $response = $this->actingAs($this->admin)->get(route('supervisor.scan-qr'));
        $response->assertForbidden();
    }

    public function test_staff_cannot_access_scan_qr(): void
    {
        $response = $this->actingAs($this->staff)->get(route('supervisor.scan-qr'));
        $response->assertForbidden();
    }

    public function test_scan_qr_shows_locations_list(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\ScanQr::class)
            ->assertSee('My Locations');
    }

    public function test_location_gets_uuid_on_create(): void
    {
        $location = Location::create([
            'name' => 'Test UUID',
            'type' => LocationType::Room,
            'created_by' => $this->supervisor->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $this->assertNotEmpty($location->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $location->uuid
        );
    }

    public function test_location_scan_url_uses_uuid(): void
    {
        $url = $this->location->scanUrl();
        $this->assertEquals(url('/r/' . $this->location->uuid), $url);
    }

    public function test_scan_qr_shows_simplified_view(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\ScanQr::class)
            ->assertSee('My Locations');
    }
}
