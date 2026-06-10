<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FormSubmissionFixedTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /**
     * Registration form submission - CSRF disabled
     */
    public function test_registration_form_submission_works(): void
    {
        Log::info('=== Testing registration with CSRF disabled ===');
        
        $initialCount = User::count();
        Log::info("Initial users: $initialCount");
        
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1234!',
            'password_confirmation' => 'Password1234!',
        ];
        
        $response = $this->post('/register', $data);
        
        Log::info("Response status: {$response->status()}");
        
        $finalCount = User::count();
        Log::info("Final users: $finalCount");
        Log::info("Users created: " . ($finalCount - $initialCount));
        
        $userExists = User::where('email', 'test@example.com')->exists();
        Log::info("User in database: " . ($userExists ? 'YES' : 'NO'));
        
        // Assert user was created
        $this->assertEquals(1, $finalCount - $initialCount, 'User should be created');
        $this->assertTrue($userExists, 'User should exist in database');
    }

    /**
     * Login form submission - CSRF disabled
     */
    public function test_login_form_submission_works(): void
    {
        Log::info('=== Testing login with CSRF disabled ===');
        
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('Password1234!'),
        ]);
        
        Log::info("Test user created: {$user->email}");
        
        $data = [
            'email' => 'login@example.com',
            'password' => 'Password1234!',
        ];
        
        $response = $this->post('/login', $data);
        
        Log::info("Response status: {$response->status()}");
        Log::info("Authenticated: " . (auth()->check() ? 'YES' : 'NO'));
        
        // Assert user is authenticated
        $this->assertTrue(auth()->check(), 'User should be authenticated');
    }

    /**
     * Password reset request - CSRF disabled
     */
    public function test_password_reset_request_works(): void
    {
        Log::info('=== Testing password reset with CSRF disabled ===');
        
        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);
        
        $data = [
            'email' => 'reset@example.com',
        ];
        
        $response = $this->post('/forgot-password', $data);
        
        Log::info("Response status: {$response->status()}");
        
        // Should redirect (302, 303, 307, 308)
        $this->assertTrue(in_array($response->status(), [302, 303, 307, 308]), 'Should redirect after password reset request');
    }
}
