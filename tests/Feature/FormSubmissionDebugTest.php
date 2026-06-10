<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FormSubmissionDebugTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Debug registration form submission - trace what happens
     */
    public function test_debug_registration_form_submission(): void
    {
        Log::info('=== DEBUG: Starting registration form submission test ===');
        
        // Get initial user count
        $initialCount = User::count();
        Log::info("Initial users in database: $initialCount");
        
        // Prepare registration data
        $data = [
            'name' => 'Debug User',
            'email' => 'debug@example.com',
            'password' => 'Password1234!',
            'password_confirmation' => 'Password1234!',
        ];
        
        Log::info('Registration data:', $data);
        
        // Submit registration form
        Log::info('Submitting POST /register');
        $response = $this->post('/register', $data);
        
        Log::info("Response status: {$response->status()}");
        
        // Check if redirected
        if ($response->isRedirect()) {
            Log::info("Redirected to: {$response->getTargetUrl()}");
        }
        
        // Check session errors using correct method
        if ($response->status() !== 302 && $response->status() !== 303) {
            Log::warning("Response is not a redirect, got {$response->status()}");
        }
        
        // Get final user count
        $finalCount = User::count();
        Log::info("Final users in database: $finalCount");
        Log::info("Users created: " . ($finalCount - $initialCount));
        
        // Check if user exists
        $userExists = User::where('email', 'debug@example.com')->exists();
        Log::info("User exists in database: " . ($userExists ? 'YES' : 'NO'));
        
        // Log response content (first 500 chars)
        $content = $response->getContent();
        Log::info('Response content length: ' . strlen($content));
        if (strlen($content) > 500) {
            Log::info('Response content (first 500 chars): ' . substr($content, 0, 500));
        } else {
            Log::info('Response content: ' . $content);
        }
        
        // Assertions
        $this->assertTrue(true, 'Debug test completed - check logs');
    }

    /**
     * Debug login form submission
     */
    public function test_debug_login_form_submission(): void
    {
        Log::info('=== DEBUG: Starting login form submission test ===');
        
        // Create a test user first
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('Password1234!'),
        ]);
        
        Log::info("Test user created: {$user->email}");
        
        // Prepare login data
        $data = [
            'email' => 'login@example.com',
            'password' => 'Password1234!',
        ];
        
        Log::info('Login data:', $data);
        
        // Check user exists before login
        $userExists = User::where('email', 'login@example.com')->exists();
        Log::info("User exists before login: " . ($userExists ? 'YES' : 'NO'));
        
        // Submit login form
        Log::info('Submitting POST /login');
        $response = $this->post('/login', $data);
        
        Log::info("Response status: {$response->status()}");
        
        // Check if redirected
        if ($response->isRedirect()) {
            Log::info("Redirected to: {$response->getTargetUrl()}");
        }
        
        // Check authentication
        Log::info("Is authenticated: " . (auth()->check() ? 'YES' : 'NO'));
        
        if (auth()->check()) {
            Log::info("Authenticated user: {auth()->user()->email}");
        }
        
        $this->assertTrue(true, 'Debug test completed - check logs');
    }

    /**
     * Debug password reset request submission
     */
    public function test_debug_password_reset_request(): void
    {
        Log::info('=== DEBUG: Starting password reset request test ===');
        
        // Create a test user
        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);
        
        Log::info("Test user created: {$user->email}");
        
        // Prepare reset request data
        $data = [
            'email' => 'reset@example.com',
        ];
        
        Log::info('Password reset request data:', $data);
        
        // Submit password reset request
        Log::info('Submitting POST /forgot-password');
        $response = $this->post('/forgot-password', $data);
        
        Log::info("Response status: {$response->status()}");
        
        // Check if redirected
        if ($response->isRedirect()) {
            Log::info("Redirected to: {$response->getTargetUrl()}");
        }
        
        Log::info('Response content (first 300 chars): ' . substr($response->getContent(), 0, 300));
        
        $this->assertTrue(true, 'Debug test completed - check logs');
    }

    /**
     * Debug: Check if middleware is running
     */
    public function test_debug_middleware_verification(): void
    {
        Log::info('=== DEBUG: Checking middleware ===');
        
        // Test CSRF middleware by checking if token is required
        $response = $this->post('/register', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'test',
            'password_confirmation' => 'test',
        ]);
        
        Log::info("POST /register without explicit CSRF: {$response->status()}");
        
        // 419 means CSRF middleware is working
        // 422 means validation is running
        // 2xx/3xx means something else happened
        
        $this->assertTrue(true, 'Middleware check completed');
    }

    /**
     * Debug: Check route model binding
     */
    public function test_debug_route_registration(): void
    {
        Log::info('=== DEBUG: Checking route registration ===');
        
        // Test that routes are registered
        $loginGet = $this->get('/login');
        Log::info("GET /login status: {$loginGet->status()}");
        
        $registerGet = $this->get('/register');
        Log::info("GET /register status: {$registerGet->status()}");
        
        // Check route names
        Log::info('Testing route helpers');
        $loginRoute = route('login');
        Log::info("route('login'): $loginRoute");
        
        $registerRoute = route('register');
        Log::info("route('register'): $registerRoute");
        
        $this->assertTrue(true, 'Route check completed');
    }
}
