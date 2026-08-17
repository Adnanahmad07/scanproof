<?php

namespace Tests\Feature;

use App\Enums\IssueStatus;
use App\Models\Issue;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5IssuesTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function createSupervisor(): User
    {
        return User::factory()->create(['role' => 'supervisor', 'is_active' => true]);
    }

    private function createStaff(): User
    {
        return User::factory()->create(['role' => 'staff', 'is_active' => true]);
    }

    private function createLocation(User $supervisor): Location
    {
        return Location::create([
            'name' => 'Test Room',
            'type' => 'room',
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'supervisor_id' => $supervisor->id,
            'created_by' => $supervisor->id,
        ]);
    }

    private function createIssue(Location $location, User $reporter): Issue
    {
        return Issue::create([
            'location_id' => $location->id,
            'description' => 'Test issue description that is long enough',
            'reported_by' => $reporter->id,
            'status' => IssueStatus::Reported,
        ]);
    }

    // ── TC-5.1: Anonymous submit creates issue ──

    public function test_tc5_1_anonymous_submit_creates_issue(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        $response = $this->postJson("/r/{$location->uuid}/report", [
            'description' => 'This is a test issue report that is long enough to pass validation.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('issues', [
            'location_id' => $location->id,
            'status' => 'reported',
        ]);
    }

    // ── TC-5.2: Description min length validated ──

    public function test_tc5_2_description_min_length_validated(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        $response = $this->postJson("/r/{$location->uuid}/report", [
            'description' => 'Short',
        ]);

        $response->assertJsonValidationErrors('description');
    }

    // ── TC-5.3: Honeypot blocks spam ──

    public function test_tc5_3_honeypot_blocks_spam(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        // Bot fills honeypot field
        $response = $this->postJson("/r/{$location->uuid}/report", [
            'description' => 'This is a spam bot trying to submit an issue.',
            'website' => 'http://spam-bot.com',
        ]);

        $response->assertRedirect();
        // No issue should be created
        $this->assertDatabaseMissing('issues', [
            'location_id' => $location->id,
        ]);
    }

    public function test_tc5_3_honeypot_empty_allows_submission(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        // Human leaves honeypot empty
        $response = $this->postJson("/r/{$location->uuid}/report", [
            'description' => 'This is a legitimate issue report that is long enough.',
            'website' => '',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('issues', [
            'location_id' => $location->id,
            'status' => 'reported',
        ]);
    }

    // ── TC-5.4: Unique SP-XXXXXX tracking code generated ──

    public function test_tc5_4_unique_tracking_code_generated(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);
        $reporter = User::factory()->create();

        $issue = $this->createIssue($location, $reporter);

        $this->assertMatchesRegularExpression('/^SP-[A-Z0-9]{6}$/', $issue->tracking_code);

        // Create another issue, ensure different code
        $issue2 = $this->createIssue($location, $reporter);
        $this->assertNotEquals($issue->tracking_code, $issue2->tracking_code);
    }

    // ── TC-5.5: /track shows status timeline only (no internal data) ──

    public function test_tc5_5_tracking_shows_status_timeline(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);
        $reporter = User::factory()->create();

        $issue = $this->createIssue($location, $reporter);

        $response = $this->get("/track/{$issue->tracking_code}");
        $response->assertStatus(200);
        $response->assertSee($issue->tracking_code);
        $response->assertSee($location->name);
    }

    // ── TC-5.6: New issue appears in supervisor feed ──

    public function test_tc5_6_new_issue_appears_in_supervisor_feed(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);
        $reporter = User::factory()->create();

        $issue = $this->createIssue($location, $reporter);

        $this->actingAs($supervisor);
        $response = $this->get('/supervisor/issues');
        $response->assertStatus(200);
        $response->assertSee($issue->tracking_code);
    }

    // ── TC-5.7: Supervisor converts issue → task / rejects with reason ──

    public function test_tc5_7_supervisor_can_reject_issue(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);
        $reporter = User::factory()->create();
        $issue = $this->createIssue($location, $reporter);

        $this->actingAs($supervisor);

        $response = $this->post("/supervisor/issues/{$issue->id}/reject", [
            'rejection_reason' => 'This is not a valid issue. The equipment is functioning normally.',
        ]);

        $response->assertRedirect();
        $issue->refresh();
        $this->assertEquals('rejected', $issue->status);
        $this->assertEquals('This is not a valid issue. The equipment is functioning normally.', $issue->rejection_reason);
    }

    public function test_tc5_7_rejection_requires_reason(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);
        $reporter = User::factory()->create();
        $issue = $this->createIssue($location, $reporter);

        $this->actingAs($supervisor);

        $response = $this->postJson("/supervisor/issues/{$issue->id}/reject", [
            'rejection_reason' => '',
        ]);

        $response->assertJsonValidationErrors('rejection_reason');
    }

    public function test_tc5_7_rejection_requires_min_5_chars(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);
        $reporter = User::factory()->create();
        $issue = $this->createIssue($location, $reporter);

        $this->actingAs($supervisor);

        $response = $this->postJson("/supervisor/issues/{$issue->id}/reject", [
            'rejection_reason' => 'No',
        ]);

        $response->assertJsonValidationErrors('rejection_reason');
    }

    public function test_tc5_7_rejection_creates_event(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);
        $reporter = User::factory()->create();
        $issue = $this->createIssue($location, $reporter);

        $this->actingAs($supervisor);

        $this->post("/supervisor/issues/{$issue->id}/reject", [
            'rejection_reason' => 'This is not a valid issue for this location.',
        ]);

        $this->assertDatabaseHas('issue_events', [
            'issue_id' => $issue->id,
            'from_status' => 'reported',
            'to_status' => 'rejected',
            'actor_id' => $supervisor->id,
            'note' => 'This is not a valid issue for this location.',
        ]);
    }

    public function test_tc5_7_non_supervisor_cannot_reject(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);
        $reporter = User::factory()->create();
        $issue = $this->createIssue($location, $reporter);

        $this->actingAs($staff);

        $response = $this->post("/supervisor/issues/{$issue->id}/reject", [
            'rejection_reason' => 'This is a test rejection reason.',
        ]);

        $response->assertStatus(403);
    }

    // ── TC-5.8: Status changes appear in public timeline ──

    public function test_tc5_8_status_changes_appear_in_timeline(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $location = $this->createLocation($supervisor);
        $reporter = User::factory()->create();
        $issue = $this->createIssue($location, $reporter);

        // Assign
        $issue->transitionTo('assigned', $supervisor->id);
        $issue->update(['assigned_to' => $staff->id]);

        // Start
        $issue->transitionTo('in_progress', $staff->id);

        $response = $this->get("/track/{$issue->tracking_code}");
        $response->assertStatus(200);
        $response->assertSee('Reported');
        $response->assertSee('Assigned');
        $response->assertSee('In Progress');
    }

    // ── Photo MIME validation ──

    public function test_tc5_photo_rejects_invalid_mime(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        // GIF should be rejected (only jpeg, png, webp allowed)
        $file = \Illuminate\Http\UploadedFile::fake()->create('test.gif', 100, 'image/gif');

        $response = $this->postJson("/r/{$location->uuid}/report", [
            'description' => 'This is a test issue with an invalid photo type.',
            'photo' => $file,
        ]);

        $response->assertJsonValidationErrors('photo');
    }

    // ── Rate limiting ──

    public function test_tc5_3_rate_limiting_blocks_excessive_reports(): void
    {
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        // Submit 5 reports (the limit)
        for ($i = 0; $i < 5; $i++) {
            $this->post("/r/{$location->uuid}/report", [
                'description' => "This is test issue number {$i} that is long enough to pass validation.",
            ]);
        }

        // 6th report should be rate limited (returns redirect with error)
        $response = $this->post("/r/{$location->uuid}/report", [
            'description' => 'This should be rate limited because we already submitted 5.',
        ]);

        $response->assertRedirect();
        // No additional issue should be created beyond the 5
        $this->assertEquals(5, Issue::where('location_id', $location->id)->count());
    }
}
