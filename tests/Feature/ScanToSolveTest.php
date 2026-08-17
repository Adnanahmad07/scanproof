<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScanToSolveTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_shows_scan_to_solve_button(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Scan to Solve');
        $response->assertSee(route('scan.homeowner'));
        $response->assertSee(route('scan.worker'));
    }

    public function test_homeowner_entry_page_is_accessible(): void
    {
        $response = $this->get('/scan/report');

        $response->assertStatus(200);
        $response->assertSee('Scan to Solve');
        $response->assertSee('Track Existing Issue');
        $response->assertSee('tracking_code');
    }

    public function test_worker_entry_redirects_guest_to_login(): void
    {
        $response = $this->get('/scan/worker');

        $response->assertRedirect(route('login'));
    }

    public function test_worker_entry_redirects_staff_to_scan(): void
    {
        $user = User::factory()->staff()->create();
        $this->actingAs($user);

        $response = $this->get('/scan/worker');

        $response->assertRedirect(route('staff.scan'));
    }

    public function test_worker_entry_redirects_supervisor_to_scan_qr(): void
    {
        $user = User::factory()->supervisor()->create();
        $this->actingAs($user);

        $response = $this->get('/scan/worker');

        $response->assertRedirect(route('supervisor.scan-qr'));
    }

    public function test_worker_entry_redirects_admin_to_locations(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $response = $this->get('/scan/worker');

        $response->assertRedirect(route('admin.locations.index'));
    }

    public function test_track_lookup_redirects_to_tracking_page(): void
    {
        $response = $this->post('/scan/track', [
            'tracking_code' => 'SP-ABC123',
        ]);

        $response->assertRedirect(route('track.show', 'SP-ABC123'));
    }

    public function test_track_lookup_requires_tracking_code(): void
    {
        $response = $this->post('/scan/track', []);

        $response->assertSessionHasErrors('tracking_code');
    }
}
