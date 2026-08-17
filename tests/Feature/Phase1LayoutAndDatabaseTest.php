<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Phase1LayoutAndDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc1_8_admin_layout_renders_sidebar_and_nav(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Scanproof');
        $response->assertSee('Dashboard');
        $response->assertSee('Supervisors');
        $response->assertSee('Locations');
        $response->assertSee('Issues');
        $response->assertSee('Logout');
    }

    public function test_tc1_9_login_page_uses_auth_layout(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('ScanProof');
        $response->assertSee('Streamline Your Facility Operations');
    }

    public function test_tc1_9_register_page_uses_auth_layout(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('ScanProof');
        $response->assertSee('Streamline Your Facility Operations');
    }

    public function test_tc1_10_database_connection_works(): void
    {
        $this->assertTrue(DB::getPdo() !== null, 'Database connection should be active');

        $result = DB::select('SELECT 1 as result');
        $this->assertEquals(1, $result[0]->result);
    }

    public function test_tc1_10_migrations_run_successfully(): void
    {
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
        $tableNames = array_column($tables, 'name');

        $this->assertContains('users', $tableNames);
        $this->assertContains('password_reset_tokens', $tableNames);
        $this->assertContains('sessions', $tableNames);
        $this->assertContains('supervisor_invitations', $tableNames);
    }
}
