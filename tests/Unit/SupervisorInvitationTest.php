<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\SupervisorInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_generation_is_unique(): void
    {
        $token1 = SupervisorInvitation::generateToken();
        $token2 = SupervisorInvitation::generateToken();

        $this->assertNotEquals($token1, $token2);
        $this->assertEquals(64, strlen($token1));
        $this->assertEquals(64, strlen($token2));
    }

    public function test_invitation_is_expired_when_past_expiry(): void
    {
        $invitation = SupervisorInvitation::create([
            'email' => 'test@example.com',
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->subHour(),
            'status' => 'pending',
        ]);

        $this->assertTrue($invitation->isExpired());
    }

    public function test_invitation_is_not_expired_when_valid(): void
    {
        $invitation = SupervisorInvitation::create([
            'email' => 'test@example.com',
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->addHours(24),
            'status' => 'pending',
        ]);

        $this->assertFalse($invitation->isExpired());
    }

    public function test_invitation_mark_accepted_updates_status(): void
    {
        $invitation = SupervisorInvitation::create([
            'email' => 'test@example.com',
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->addHours(24),
            'status' => 'pending',
        ]);

        $invitation->markAccepted();

        $this->assertEquals('accepted', $invitation->fresh()->status);
        $this->assertNotNull($invitation->fresh()->accepted_at);
    }

    public function test_invitation_mark_expired_updates_status(): void
    {
        $invitation = SupervisorInvitation::create([
            'email' => 'test@example.com',
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->addHours(24),
            'status' => 'pending',
        ]);

        $invitation->markExpired();

        $this->assertEquals('expired', $invitation->fresh()->status);
    }

    public function test_invitation_is_valid_for_use_when_pending_and_not_expired(): void
    {
        $invitation = SupervisorInvitation::create([
            'email' => 'test@example.com',
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->addHours(24),
            'status' => 'pending',
        ]);

        $this->assertTrue($invitation->isValidForUse());
    }

    public function test_invitation_is_not_valid_for_use_when_accepted(): void
    {
        $invitation = SupervisorInvitation::create([
            'email' => 'test@example.com',
            'token' => SupervisorInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->addHours(24),
            'status' => 'accepted',
        ]);

        $this->assertFalse($invitation->isValidForUse());
    }

    public function test_user_role_enum_has_correct_values(): void
    {
        $this->assertEquals('admin', UserRole::Admin->value);
        $this->assertEquals('supervisor', UserRole::Supervisor->value);
        $this->assertEquals('staff', UserRole::Staff->value);
        $this->assertEquals('client', UserRole::Client->value);
    }

    public function test_user_role_enum_has_correct_labels(): void
    {
        $this->assertEquals('System Admin', UserRole::Admin->label());
        $this->assertEquals('Supervisor', UserRole::Supervisor->label());
        $this->assertEquals('Staff', UserRole::Staff->label());
        $this->assertEquals('Client', UserRole::Client->label());
    }

    public function test_user_role_enum_has_correct_dashboard_paths(): void
    {
        $this->assertEquals('/admin/dashboard', UserRole::Admin->dashboardPath());
        $this->assertEquals('/supervisor/dashboard', UserRole::Supervisor->dashboardPath());
        $this->assertEquals('/staff/dashboard', UserRole::Staff->dashboardPath());
        $this->assertEquals('/client/dashboard', UserRole::Client->dashboardPath());
    }
}
