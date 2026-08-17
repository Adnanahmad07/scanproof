<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc1_6_guest_redirected_to_login_from_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_tc1_6_staff_gets_403_on_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_tc1_6_supervisor_gets_403_on_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'supervisor']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_tc1_6_admin_accesses_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_tc1_7_admin_dashboard_renders_content(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Welcome back');
        $response->assertSee('Total Staff');
        $response->assertSee('Active Tasks');
        $response->assertSee('Locations');
        $response->assertSee('Quick Actions');
    }

    public function test_tc_ki_2_dashboard_redirects_by_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertRedirect('/admin/dashboard');

        $response = $this->actingAs($supervisor)->get('/dashboard');
        $response->assertRedirect('/supervisor/dashboard');

        $response = $this->actingAs($staff)->get('/dashboard');
        $response->assertRedirect('/staff/dashboard');
    }
}
