<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Models\Issue;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorConvertToTaskTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $supervisor;
    protected User $staff;
    protected Location $location;
    protected Issue $issue;

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

        $this->issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'The light bulb is broken in the meeting room',
            'status' => 'assigned',
            'assigned_to' => $this->staff->id,
        ]);
    }

    // TC-P3.1: Convert to task with custom category
    public function test_convert_to_task_with_custom_category(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $this->issue), [
                'category' => 'cleaning',
                'priority' => 'medium',
            ]);

        $this->assertDatabaseHas('tasks', [
            'issue_id' => $this->issue->id,
            'category' => 'cleaning',
        ]);
    }

    // TC-P3.2: Convert to task with custom priority
    public function test_convert_to_task_with_custom_priority(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $this->issue), [
                'category' => 'maintenance',
                'priority' => 'urgent',
            ]);

        $this->assertDatabaseHas('tasks', [
            'issue_id' => $this->issue->id,
            'priority' => 'urgent',
        ]);
    }

    // TC-P3.3: Convert to task with due date
    public function test_convert_to_task_with_due_date(): void
    {
        $dueDate = now()->addDays(3)->format('Y-m-d');

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $this->issue), [
                'category' => 'maintenance',
                'priority' => 'medium',
                'due_date' => $dueDate,
            ]);

        $this->assertDatabaseHas('tasks', [
            'issue_id' => $this->issue->id,
            'due_date' => $dueDate . ' 00:00:00',
        ]);
    }

    // TC-P3.4: Convert to task without due date
    public function test_convert_to_task_without_due_date(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $this->issue), [
                'category' => 'maintenance',
                'priority' => 'medium',
            ]);

        $this->assertDatabaseHas('tasks', [
            'issue_id' => $this->issue->id,
            'due_date' => null,
        ]);
    }

    // TC-P3.5: Invalid category rejected
    public function test_invalid_category_rejected(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $this->issue), [
                'category' => 'invalid_category',
                'priority' => 'medium',
            ]);

        $response->assertSessionHasErrors('category');
    }

    // TC-P3.6: Invalid priority rejected
    public function test_invalid_priority_rejected(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $this->issue), [
                'category' => 'maintenance',
                'priority' => 'invalid_priority',
            ]);

        $response->assertSessionHasErrors('priority');
    }

    // TC-P3.7: Past due date rejected
    public function test_past_due_date_rejected(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $this->issue), [
                'category' => 'maintenance',
                'priority' => 'medium',
                'due_date' => now()->subDay()->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('due_date');
    }

    // TC-P3.8: Task links to issue via issue_id
    public function test_task_links_to_issue_via_issue_id(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.issues.convert-to-task', $this->issue), [
                'category' => 'maintenance',
                'priority' => 'high',
            ]);

        $task = \App\Models\Task::where('issue_id', $this->issue->id)->first();
        $this->assertNotNull($task);
        $this->assertEquals($this->issue->id, $task->issue_id);
    }
}
