<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc1_1_landing_page_displays_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('ScanProof');
        $response->assertSee('Facility Operations Platform');
        $response->assertSee('Streamline Your');
    }

    public function test_tc1_2_landing_page_navigation_links_work(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Features');
        $response->assertSee('How It Works');
        $response->assertSee(route('pricing'));
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
    }

    public function test_tc_ki_1_registering_never_produces_admin_user(): void
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password1234!',
            'password_confirmation' => 'Password1234!',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role' => UserRole::Staff->value,
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@example.com',
            'role' => UserRole::Admin->value,
        ]);
    }
}
