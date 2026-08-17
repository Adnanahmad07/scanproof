<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IssueFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $supervisor;
    protected User $staff;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->supervisor = User::factory()->supervisor()->create();
        $this->staff = User::factory()->staff()->create([
            'supervisor_id' => $this->supervisor->id,
        ]);

        $this->location = Location::create([
            'name' => 'Test Room',
            'type' => LocationType::Room,
            'building' => 'Test Building',
            'configured' => true,
            'configured_by' => $this->supervisor->id,
            'configured_at' => now(),
            'created_by' => $this->admin->id,
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    // ── CONVERT ISSUE TO TASK ──

    public function test_supervisor_can_convert_issue_to_task(): void
    {
        $issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'The light is broken in the conference room',
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $issue->id), [
                'category' => 'maintenance',
                'priority' => 'high',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'issue_id' => $issue->id,
            'location_id' => $this->location->id,
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'status' => 'pending',
        ]);

        $task = Task::where('issue_id', $issue->id)->first();
        $this->assertNotNull($task);
        $this->assertEquals('The light is broken in the conference room', $task->description);
    }

    public function test_converted_task_has_correct_data_from_issue(): void
    {
        $issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Leaky faucet in the bathroom needs repair',
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $issue->id), [
                'category' => 'cleaning',
                'priority' => 'urgent',
            ]);

        $task = Task::where('issue_id', $issue->id)->first();

        $this->assertEquals($issue->description, $task->description);
        $this->assertEquals($this->location->name, $task->location);
        $this->assertEquals($this->location->id, $task->location_id);
        $this->assertEquals($issue->assigned_to, $task->assigned_to);
        $this->assertEquals($this->supervisor->id, $task->supervisor_id);
        $this->assertEquals('cleaning', $task->category->value);
        $this->assertEquals('urgent', $task->priority->value);
        $this->assertEquals('pending', $task->status->value);
    }

    public function test_supervisor_cannot_convert_already_converted_issue(): void
    {
        $issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Already converted issue',
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $issue->id), [
                'category' => 'maintenance',
                'priority' => 'medium',
            ]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $issue->id), [
                'category' => 'maintenance',
                'priority' => 'medium',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This issue has already been converted to a task.');

        $this->assertEquals(1, Task::where('issue_id', $issue->id)->count());
    }

    public function test_supervisor_cannot_convert_unassigned_reported_issue(): void
    {
        $issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Unassigned reported issue',
            'status' => 'reported',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $issue->id));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Assign a worker to this issue before converting to a task.');

        $this->assertDatabaseMissing('tasks', ['issue_id' => $issue->id]);
    }

    public function test_supervisor_can_convert_reported_issue_with_worker(): void
    {
        $issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Reported issue with worker assigned',
            'status' => 'reported',
            'assigned_to' => $this->staff->id,
        ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $issue->id), [
                'category' => 'maintenance',
                'priority' => 'medium',
            ]);

        $this->assertDatabaseHas('tasks', [
            'issue_id' => $issue->id,
            'assigned_to' => $this->staff->id,
        ]);

        $issue->refresh();
        $this->assertEquals('assigned', $issue->status);
    }

    public function test_non_supervisor_cannot_convert_issue_to_task(): void
    {
        $issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Test issue',
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->post(route('supervisor.issues.convert-to-task', $issue->id));

        $response->assertForbidden();
    }

    // ── STAFF QR SCAN → FIND ISSUES ──

    public function test_worker_can_scan_qr_and_find_issues_at_location(): void
    {
        $issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Assigned issue at this location',
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\ScanScreen::class)
            ->set('scanCode', $this->location->uuid)
            ->call('scanCode')
            ->assertSee('Assigned issue at this location')
            ->assertSee('Assigned');
    }

    public function test_worker_can_start_issue_from_scan(): void
    {
        $issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Issue to start',
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\ScanScreen::class)
            ->set('scanCode', $this->location->uuid)
            ->call('scanCode')
            ->assertSee('Issue to start')
            ->call('startIssue', $issue->id);

        $this->assertDatabaseHas('issues', [
            'id' => $issue->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_worker_can_complete_issue_from_scan(): void
    {
        $issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Issue to complete',
            'status' => 'in_progress',
            'assigned_to' => $this->staff->id,
        ]);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\ScanScreen::class)
            ->set('scanCode', $this->location->uuid)
            ->call('scanCode')
            ->assertSee('Issue to complete')
            ->call('completeIssue', $issue->id);

        $this->assertDatabaseHas('issues', [
            'id' => $issue->id,
            'status' => 'resolved',
        ]);
    }

    public function test_worker_cannot_see_other_workers_issues_at_location(): void
    {
        $otherStaff = User::factory()->staff()->create([
            'supervisor_id' => $this->supervisor->id,
        ]);

        Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Not my issue',
            'status' => 'assigned',
            'assigned_to' => $otherStaff->id,
        ]);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\ScanScreen::class)
            ->set('scanCode', $this->location->uuid)
            ->call('scanCode')
            ->assertDontSee('Not my issue');
    }

    public function test_scan_invalid_uuid_shows_error(): void
    {
        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\ScanScreen::class)
            ->set('scanCode', 'invalid-uuid')
            ->call('scanCode')
            ->assertHasErrors('scanCode');
    }

    public function test_scan_location_with_no_issues_shows_empty(): void
    {
        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\ScanScreen::class)
            ->set('scanCode', $this->location->uuid)
            ->call('scanCode')
            ->assertSee('No issues assigned to you here');
    }
}
