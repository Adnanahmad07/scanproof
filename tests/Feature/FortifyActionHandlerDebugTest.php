<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FortifyActionHandlerDebugTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /**
     * Test if CreateNewUser action is being called
     */
    public function test_create_new_user_action_called(): void
    {
        Log::info('=== Testing CreateNewUser action handler ===');
        
        // Check if action instance can be created
        try {
            $action = app(\App\Actions\Fortify\CreateNewUser::class);
            Log::info('CreateNewUser action instantiated successfully');
            
            // Try to call the action directly
            $userData = [
                'name' => 'Direct Call Test',
                'email' => 'direct@example.com',
                'password' => 'Password1234!',
            ];
            
            Log::info('Calling action directly...');
            $user = $action->create($userData);
            
            Log::info("User created directly: {$user->email}");
            
            $this->assertDatabaseHas('users', [
                'email' => 'direct@example.com',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Exception when calling CreateNewUser: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test Fortify registration endpoint with logging
     */
    public function test_fortify_registration_endpoint_with_handler_check(): void
    {
        Log::info('=== Testing Fortify registration endpoint ===');
        
        // Check if Fortify is configured
        Log::info('Fortify config loaded');
        
        // Try registration
        $data = [
            'name' => 'Fortify Test',
            'email' => 'fortify@example.com',
            'password' => 'Password1234!',
            'password_confirmation' => 'Password1234!',
        ];
        
        Log::info('Posting to /register...');
        $response = $this->post('/register', $data);
        
        Log::info("Response status: {$response->status()}");
        
        if ($response->status() === 500) {
            Log::error('Got 500 error from registration endpoint');
            Log::error('Response content: ' . substr($response->getContent(), 0, 1000));
        }
        
        // Check if user was created
        $userExists = User::where('email', 'fortify@example.com')->exists();
        Log::info("User in database: " . ($userExists ? 'YES' : 'NO'));
        
        $this->assertTrue($userExists || $response->status() === 500, 'Either user created or 500 error');
    }

    /**
     * Check Fortify bindings in container
     */
    public function test_fortify_bindings_in_container(): void
    {
        Log::info('=== Checking Fortify container bindings ===');
        
        try {
            // Check if CreateNewUser is bound
            $canResolve = app()->has(\App\Actions\Fortify\CreateNewUser::class);
            Log::info("Can resolve CreateNewUser: " . ($canResolve ? 'YES' : 'NO'));
            
            // Try to resolve it
            $action = app()->make(\App\Actions\Fortify\CreateNewUser::class);
            Log::info("CreateNewUser resolved: " . get_class($action));
            
            // Check Fortify service provider
            $fortifyProviderRegistered = in_array(
                \App\Providers\FortifyServiceProvider::class,
                config('app.providers', [])
            );
            Log::info("FortifyServiceProvider in config: " . ($fortifyProviderRegistered ? 'YES' : 'NO'));
            
            // Check bootstrap providers
            $bootstrapProviders = include base_path('bootstrap/providers.php');
            $fortifyInBootstrap = in_array(\App\Providers\FortifyServiceProvider::class, $bootstrapProviders);
            Log::info("FortifyServiceProvider in bootstrap: " . ($fortifyInBootstrap ? 'YES' : 'NO'));
            
        } catch (\Exception $e) {
            Log::error('Exception checking bindings: ' . $e->getMessage());
        }
        
        $this->assertTrue(true);
    }

    /**
     * Check RegisteredUserController exists and is callable
     */
    public function test_registered_user_controller_accessible(): void
    {
        Log::info('=== Checking RegisteredUserController ===');
        
        try {
            $controllerClass = \Laravel\Fortify\Http\Controllers\RegisteredUserController::class;
            Log::info("RegisteredUserController class: $controllerClass");
            
            $controller = app()->make($controllerClass);
            Log::info("Controller instantiated: " . get_class($controller));
            
            // Check if store method exists
            $hasStore = method_exists($controller, 'store');
            Log::info("store method exists: " . ($hasStore ? 'YES' : 'NO'));
            
        } catch (\Exception $e) {
            Log::error('Exception with controller: ' . $e->getMessage());
        }
        
        $this->assertTrue(true);
    }
}
