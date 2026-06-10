<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\SupervisorInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SupervisorInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);
    }

    public function test_admin_can_access_supervisor_management_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.supervisors.index'));

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_supervisor_management(): void
    {
        $response = $this->get(route('admin.supervisors.index'));

        $response->assertRedirect('/login');
    }

    public function test_admin_can_send_invitation(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->post(route('admin.supervisors.invite'), [
            'email' => 'new-supervisor@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('supervisor_invitations', [
            'email' => 'new-supervisor@example.com',
            'invited_by' => $this->admin->id,
            'status' => 'pending',
        ]);

        Mail::assertSent(\App\Mail\SupervisorInvitationMail::class, function ($mail) {
            return $mail->hasTo('new-supervisor@example.com');
        });
    }

    public function test_cannot_invite_existing_user(): void
    {
        Mail::fake();

        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->admin)->post(route('admin.supervisors.invite'), [
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseMissing('supervisor_invitations', [
            'email' => 'existing@example.com',
        ]);
    }

    public function test_cannot_send_duplicate_invitation(): void
    {
        Mail::fake();

        SupervisorInvitation::create([
            'email' => 'pending@example.com',
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => $this->admin->id,
            'expires_at' => now()->addHours(48),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.supervisors.invite'), [
            'email' => 'pending@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_non_admin_cannot_send_invitation(): void
    {
        Mail::fake();

        $staff = User::factory()->create([
            'role' => UserRole::Staff,
        ]);

        $response = $this->actingAs($staff)->post(route('admin.supervisors.invite'), [
            'email' => 'new-supervisor@example.com',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_cancel_pending_invitation(): void
    {
        $invitation = SupervisorInvitation::create([
            'email' => 'cancel@example.com',
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => $this->admin->id,
            'expires_at' => now()->addHours(48),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.supervisors.cancel', $invitation));

        $response->assertRedirect();
        $this->assertDatabaseHas('supervisor_invitations', [
            'id' => $invitation->id,
            'status' => 'expired',
        ]);
    }

    public function test_supervisor_set_password_page_is_accessible_with_valid_token(): void
    {
        $invitation = SupervisorInvitation::create([
            'email' => 'new-supervisor@example.com',
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => $this->admin->id,
            'expires_at' => now()->addHours(48),
            'status' => 'pending',
        ]);

        $response = $this->get(route('supervisor.set-password', $invitation->token));

        $response->assertStatus(200);
    }

    public function test_expired_token_shows_error(): void
    {
        $invitation = SupervisorInvitation::create([
            'email' => 'expired@example.com',
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => $this->admin->id,
            'expires_at' => now()->subHours(1),
            'status' => 'pending',
        ]);

        $response = $this->get(route('supervisor.set-password', $invitation->token));

        $response->assertStatus(410);
    }

    public function test_invalid_token_shows_404(): void
    {
        $response = $this->get('/supervisor/set-password/invalid-token-12345');

        $response->assertStatus(404);
    }
}
