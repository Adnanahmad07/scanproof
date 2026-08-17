<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Enums\TaskCategory;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Location;
use App\Models\Task;
use App\Models\TaskPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StaffDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;
    protected User $staff;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->supervisor = User::factory()->supervisor()->create();
        $this->staff = User::factory()->staff()->create([
            'supervisor_id' => $this->supervisor->id,
        ]);

        Task::create([
            'title' => 'Test Task',
            'location' => 'Room-101',
            'category' => TaskCategory::Cleaning,
            'priority' => TaskPriority::Medium,
            'status' => TaskStatus::Pending,
            'supervisor_id' => $this->supervisor->id,
            'assigned_to' => $this->staff->id,
        ]);
    }

    public function test_staff_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_unauthenticated_user_cannot_access_staff_dashboard(): void
    {
        $response = $this->get(route('staff.dashboard'));

        $response->assertRedirect('/login');
    }

    public function test_non_staff_cannot_access_staff_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('staff.dashboard'));

        $response->assertForbidden();
    }

    public function test_supervisor_cannot_access_staff_dashboard(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('staff.dashboard'));

        $response->assertForbidden();
    }

    public function test_staff_can_access_tasks_page(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.tasks'));

        $response->assertStatus(200);
        $response->assertSee('My Tasks');
    }

    public function test_staff_can_access_scan_page(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.scan'));

        $response->assertStatus(200);
        $response->assertSee('Scan QR');
    }

    public function test_staff_dashboard_shows_task_stats(): void
    {
        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\Dashboard::class)
            ->assertSee('Total Tasks')
            ->assertSee('Pending')
            ->assertSee('In Progress')
            ->assertSee('Completed');
    }

    public function test_staff_task_list_shows_assigned_tasks(): void
    {
        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskList::class)
            ->assertSee('Test Task')
            ->assertSee('Room-101');
    }

    public function test_staff_can_start_task(): void
    {
        $task = Task::where('assigned_to', $this->staff->id)->first();

        TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'before',
            'path' => 'task-photos/' . $task->id . '/before.jpg',
        ]);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskList::class)
            ->call('startTask', $task->id);

        $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
                'status' => TaskStatus::InProgress,
            ]);
    }

    public function test_staff_can_complete_task(): void
    {
        $task = Task::where('assigned_to', $this->staff->id)->first();
        $task->update(['status' => TaskStatus::InProgress]);

        TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'before',
            'path' => 'task-photos/' . $task->id . '/before.jpg',
        ]);

        TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'after',
            'path' => 'task-photos/' . $task->id . '/after.jpg',
        ]);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskList::class)
            ->call('completeTask', $task->id);

        $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
                'status' => TaskStatus::Completed,
            ]);
    }

    public function test_staff_can_search_task_by_code(): void
    {
        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\ScanScreen::class)
            ->set('scanCode', 'NONEXISTENT-UUID')
            ->call('scanCode')
            ->assertHasErrors(['scanCode']);
    }

    public function test_nonexistent_code_shows_error(): void
    {
        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\ScanScreen::class)
            ->set('scanCode', 'NONEXISTENT')
            ->call('scanCode')
            ->assertHasErrors(['scanCode']);
    }

    public function test_supervisor_can_create_task(): void
    {
        $worker = User::factory()->staff()->create([
            'supervisor_id' => $this->supervisor->id,
        ]);

        $location = Location::create([
            'name' => 'Test Location',
            'type' => LocationType::Room,
            'configured' => true,
            'supervisor_id' => $this->supervisor->id,
            'created_by' => $this->admin->id,
        ]);

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\TaskAssignment::class)
            ->set('taskTitle', 'New Task')
            ->set('taskLocationId', $location->id)
            ->set('taskAssignedTo', $worker->id)
            ->call('createTask');

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'assigned_to' => $worker->id,
        ]);
    }

    public function test_supervisor_cannot_create_task_without_required_fields(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\TaskAssignment::class)
            ->set('taskTitle', '')
            ->set('taskLocationId', '')
            ->set('taskAssignedTo', '')
            ->call('createTask')
            ->assertHasErrors(['taskTitle', 'taskLocationId', 'taskAssignedTo']);
    }

    public function test_non_supervisor_cannot_create_task(): void
    {
        $response = $this->actingAs($this->staff)->get(route('supervisor.tasks'));

        $response->assertForbidden();
    }
}
