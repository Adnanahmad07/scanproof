<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2UserManagementTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──

    private function createAdmin(): User
    {
        return User::factory()->admin()->create(['is_active' => true]);
    }

    private function createStaff(): User
    {
        return User::factory()->staff()->create(['is_active' => true]);
    }

    private function createSupervisor(): User
    {
        return User::factory()->supervisor()->create(['is_active' => true]);
    }

    // ── TC-2.1: Admin lists users ──

    public function test_tc2_1_admin_can_access_user_management_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Users');
    }

    public function test_tc2_1_admin_sees_users_in_list(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee($staff->name);
        $response->assertSee($staff->email);
    }

    // ── TC-2.2: Admin creates user (always staff) ──

    public function test_tc2_2_admin_can_create_user(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'createName' => 'New Worker',
            'createEmail' => 'worker@example.com',
            'createPassword' => 'Password1234!',
            'createPassword_confirmation' => 'Password1234!',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'worker@example.com',
            'role' => 'staff',
            'is_active' => true,
        ]);
    }

    public function test_tc2_2_created_user_always_has_staff_role(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'createName' => 'New Person',
            'createEmail' => 'newperson@example.com',
            'createPassword' => 'Password1234!',
            'createPassword_confirmation' => 'Password1234!',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newperson@example.com',
            'role' => 'staff',
        ]);
    }

    // ── TC-2.3: Admin edits user ──

    public function test_tc2_3_admin_can_edit_user(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $response = $this->actingAs($admin)->post("/admin/users/{$staff->id}/update", [
            'editName' => 'Updated Name',
            'editEmail' => $staff->email,
            'editRole' => 'staff',
            'editIsActive' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_tc2_3_admin_can_change_user_email(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $response = $this->actingAs($admin)->post("/admin/users/{$staff->id}/update", [
            'editName' => $staff->name,
            'editEmail' => 'newemail@example.com',
            'editRole' => 'staff',
            'editIsActive' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'email' => 'newemail@example.com',
        ]);
    }

    // ── TC-2.4: Admin deactivates user (blocked from login) ──

    public function test_tc2_4_admin_can_deactivate_user(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $response = $this->actingAs($admin)->post("/admin/users/{$staff->id}/toggle-active");

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'is_active' => false,
        ]);
    }

    public function test_tc2_4_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->staff()->create([
            'is_active' => false,
            'password' => bcrypt('Password1234!'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password1234!',
        ]);

        $this->assertGuest();
    }

    public function test_tc2_4_admin_can_reactivate_user(): void
    {
        $admin = $this->createAdmin();
        $staff = User::factory()->staff()->create(['is_active' => false]);

        $response = $this->actingAs($admin)->post("/admin/users/{$staff->id}/toggle-active");

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'is_active' => true,
        ]);
    }

    public function test_tc2_4_admin_cannot_deactivate_self(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post("/admin/users/{$admin->id}/toggle-active");

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'is_active' => true,
        ]);
    }

    // ── TC-2.5: Non-admin gets 403 on user CRUD routes ──

    public function test_tc2_5_staff_gets_403_on_user_management(): void
    {
        $staff = $this->createStaff();

        $response = $this->actingAs($staff)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_tc2_5_supervisor_gets_403_on_user_management(): void
    {
        $supervisor = $this->createSupervisor();

        $response = $this->actingAs($supervisor)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_tc2_5_guest_gets_redirect_on_user_management(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }

    // ── TC-2.6: Admin changes user role; redirect follows ──

    public function test_tc2_6_admin_can_change_user_role(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $response = $this->actingAs($admin)->post("/admin/users/{$staff->id}/update", [
            'editName' => $staff->name,
            'editEmail' => $staff->email,
            'editRole' => 'supervisor',
            'editIsActive' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'role' => 'supervisor',
        ]);
    }

    public function test_tc2_6_role_change_reflects_in_dashboard_redirect(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        $this->actingAs($admin)->post("/admin/users/{$staff->id}/update", [
            'editName' => $staff->name,
            'editEmail' => $staff->email,
            'editRole' => 'supervisor',
            'editIsActive' => true,
        ]);

        $response = $this->actingAs($staff->fresh())->get('/dashboard');
        $response->assertRedirect('/supervisor/dashboard');
    }

    // ── TC-2.7: User cannot change own role ──

    public function test_tc2_7_user_cannot_change_own_role_via_update(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post("/admin/users/{$admin->id}/update", [
            'editName' => $admin->name,
            'editEmail' => $admin->email,
            'editRole' => 'staff',
            'editIsActive' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }

    // ── TC-2.8: User updates name/email (Fortify profile) ──

    public function test_tc2_8_user_can_update_profile_information(): void
    {
        $user = $this->createStaff();

        $response = $this->actingAs($user)->put('/user/profile-information', [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    // ── TC-2.9: Password change requires current password ──

    public function test_tc2_9_password_change_requires_current_password(): void
    {
        $user = $this->createStaff();

        $response = $this->actingAs($user)->put('/user/password', [
            'current_password' => 'wrongpassword',
            'password' => 'NewPassword1234!',
            'password_confirmation' => 'NewPassword1234!',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', ['current_password']);
    }

    public function test_tc2_9_password_change_works_with_correct_current_password(): void
    {
        $user = User::factory()->staff()->create([
            'password' => bcrypt('OldPassword1234!'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put('/user/password', [
            'current_password' => 'OldPassword1234!',
            'password' => 'NewPassword1234!',
            'password_confirmation' => 'NewPassword1234!',
        ]);

        $this->assertTrue(password_verify('NewPassword1234!', $user->fresh()->password));
    }

    // ── TC-2.10: Invalid input rejected with validation errors ──

    public function test_tc2_10_create_user_rejects_invalid_email(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'createName' => 'Test User',
            'createEmail' => 'not-an-email',
            'createPassword' => 'Password1234!',
            'createPassword_confirmation' => 'Password1234!',
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'not-an-email']);
    }

    public function test_tc2_10_create_user_rejects_weak_password(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'createName' => 'Test User',
            'createEmail' => 'test@example.com',
            'createPassword' => 'weak',
            'createPassword_confirmation' => 'weak',
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_tc2_10_create_user_rejects_duplicate_email(): void
    {
        $admin = $this->createAdmin();
        $existing = $this->createStaff();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'createName' => 'Duplicate User',
            'createEmail' => $existing->email,
            'createPassword' => 'Password1234!',
            'createPassword_confirmation' => 'Password1234!',
        ]);

        $this->assertEquals(1, User::where('email', $existing->email)->count());
    }

    // ── TC-2.12: Client dashboard access control ──

    public function test_tc2_12_client_can_access_client_dashboard(): void
    {
        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);

        $response = $this->actingAs($client)->get('/client/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_tc2_12_staff_gets_403_on_client_dashboard(): void
    {
        $staff = $this->createStaff();

        $response = $this->actingAs($staff)->get('/client/dashboard');

        $response->assertStatus(403);
    }

    public function test_tc2_12_supervisor_gets_403_on_client_dashboard(): void
    {
        $supervisor = $this->createSupervisor();

        $response = $this->actingAs($supervisor)->get('/client/dashboard');

        $response->assertStatus(403);
    }

    public function test_tc2_12_admin_gets_403_on_client_dashboard(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/client/dashboard');

        $response->assertStatus(403);
    }

    public function test_tc2_12_guest_redirected_from_client_dashboard(): void
    {
        $response = $this->get('/client/dashboard');

        $response->assertRedirect('/login');
    }
}
