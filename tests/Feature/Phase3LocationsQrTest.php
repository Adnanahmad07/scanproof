<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Models\Location;
use App\Models\ScanVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3LocationsQrTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->admin()->create(['is_active' => true]);
    }

    private function createSupervisor(): User
    {
        return User::factory()->supervisor()->create(['is_active' => true]);
    }

    // ── TC-3.1: Location CRUD works for admin ──

    public function test_tc3_1_admin_can_access_location_management(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/locations');

        $response->assertStatus(200);
    }

    public function test_tc3_1_location_gets_uuid_on_create(): void
    {
        $admin = $this->createAdmin();

        $location = Location::create([
            'name' => 'Test Building',
            'type' => LocationType::Building,
            'created_by' => $admin->id,
        ]);

        $this->assertNotNull($location->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $location->uuid
        );
    }

    public function test_tc3_1_location_scan_url_uses_uuid(): void
    {
        $admin = $this->createAdmin();

        $location = Location::create([
            'name' => 'Scan Room',
            'type' => LocationType::Room,
            'created_by' => $admin->id,
        ]);

        $url = $location->scanUrl();
        $this->assertStringStartsWith(url('/r/'), $url);
        $this->assertStringContainsString($location->uuid, $url);
    }

    // ── TC-3.2: Duplicate (name+building+floor) rejected ──

    public function test_tc3_2_duplicate_location_rejected_at_database_level(): void
    {
        $admin = $this->createAdmin();

        $building = Location::create([
            'name' => 'Building A',
            'type' => LocationType::Building,
            'created_by' => $admin->id,
        ]);

        Location::create([
            'name' => 'Floor 1',
            'type' => LocationType::Floor,
            'parent_id' => $building->id,
            'created_by' => $admin->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Location::create([
            'name' => 'Floor 1',
            'type' => LocationType::Floor,
            'parent_id' => $building->id,
            'created_by' => $admin->id,
        ]);
    }

    public function test_tc3_2_same_name_different_type_allowed(): void
    {
        $admin = $this->createAdmin();

        Location::create([
            'name' => 'Main',
            'type' => LocationType::Building,
            'created_by' => $admin->id,
        ]);

        $location2 = Location::create([
            'name' => 'Main',
            'type' => LocationType::Floor,
            'created_by' => $admin->id,
        ]);

        $this->assertNotNull($location2->id);
    }

    // ── TC-3.5: Printable QR PDF/sheet generated ──

    public function test_tc3_5_qr_print_route_exists(): void
    {
        $admin = $this->createAdmin();
        $location = Location::create([
            'name' => 'Print Room',
            'type' => LocationType::Room,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get("/admin/locations/print?ids[]={$location->id}");

        $response->assertStatus(200);
    }

    public function test_tc3_5_qr_pdf_route_exists(): void
    {
        $admin = $this->createAdmin();
        $location = Location::create([
            'name' => 'PDF Room',
            'type' => LocationType::Room,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get("/admin/locations/pdf?ids[]={$location->id}");

        $response->assertStatus(200);
    }

    public function test_tc3_5_non_admin_cannot_access_qr_print(): void
    {
        $staff = User::factory()->staff()->create(['is_active' => true]);

        $response = $this->actingAs($staff)->get('/admin/locations/print');

        $response->assertStatus(403);
    }

    // ── TC-3.6: GET /r/{uuid} resolves location ──

    public function test_tc3_6_scan_resolves_location(): void
    {
        $location = Location::create([
            'name' => 'Test Room',
            'type' => LocationType::Room,
        ]);

        $response = $this->get("/r/{$location->uuid}");

        $response->assertStatus(200);
    }

    // ── TC-3.7: Invalid uuid → 404 ──

    public function test_tc3_7_invalid_uuid_returns_404(): void
    {
        $response = $this->get('/r/nonexistent-uuid');

        $response->assertStatus(404);
    }

    // ── TC-3.8: Scans/visits recorded with timestamp ──

    public function test_tc3_8_scan_visit_recorded_on_qr_scan(): void
    {
        $location = Location::create([
            'name' => 'Track Room',
            'type' => LocationType::Room,
        ]);

        $this->assertDatabaseCount('scan_visits', 0);

        $response = $this->get("/r/{$location->uuid}");

        $this->assertDatabaseCount('scan_visits', 1);
        $this->assertDatabaseHas('scan_visits', [
            'location_id' => $location->id,
            'uuid' => $location->uuid,
        ]);
    }

    public function test_tc3_8_scan_visit_records_ip_and_user_agent(): void
    {
        $location = Location::create([
            'name' => 'IP Room',
            'type' => LocationType::Room,
        ]);

        $this->get("/r/{$location->uuid}", [
            'HTTP_USER_AGENT' => 'TestAgent/1.0',
        ]);

        $visit = ScanVisit::where('location_id', $location->id)->first();
        $this->assertNotNull($visit);
        $this->assertNotNull($visit->ip_address);
        $this->assertEquals('TestAgent/1.0', $visit->user_agent);
    }

    public function test_tc3_8_multiple_scans_recorded(): void
    {
        $location = Location::create([
            'name' => 'Multi Room',
            'type' => LocationType::Room,
        ]);

        $this->get("/r/{$location->uuid}");
        $this->get("/r/{$location->uuid}");
        $this->get("/r/{$location->uuid}");

        $this->assertEquals(3, ScanVisit::where('location_id', $location->id)->count());
    }

    // ── TC-3.9: History visible per location ──

    public function test_tc3_9_location_has_scan_visits_relationship(): void
    {
        $location = Location::create([
            'name' => 'Rel Room',
            'type' => LocationType::Room,
        ]);

        $this->get("/r/{$location->uuid}");
        $this->get("/r/{$location->uuid}");

        $this->assertEquals(2, $location->scanVisits()->count());
    }

    public function test_tc3_9_scan_visits_ordered_by_creation(): void
    {
        $location = Location::create([
            'name' => 'Order Room',
            'type' => LocationType::Room,
        ]);

        $this->get("/r/{$location->uuid}");
        $this->get("/r/{$location->uuid}");

        $visits = $location->scanVisits()->get();
        $this->assertTrue($visits->first()->created_at->lte($visits->last()->created_at));
    }
}
