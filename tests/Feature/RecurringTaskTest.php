<?php

namespace Tests\Feature;

use App\Console\Commands\GenerateRecurringTasks;
use App\Enums\TaskCategory;
use App\Enums\TaskPriority;
use App\Enums\UserRole;
use App\Models\Location;
use App\Models\RecurringTask;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class RecurringTaskTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;
    private User $staff;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = User::factory()->supervisor()->create(['is_active' => true]);
        $this->staff = User::factory()->staff()->create([
            'is_active' => true,
            'supervisor_id' => $this->supervisor->id,
        ]);
        $this->location = Location::create([
            'name' => 'Lobby',
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'supervisor_id' => $this->supervisor->id,
            'created_by' => $this->supervisor->id,
        ]);
    }

    // ── P3-TC01: RecurringTask model creates successfully ──

    public function test_p3_tc01_recurring_task_model_creates(): void
    {
        $task = RecurringTask::create([
            'title' => 'Daily Floor Cleaning',
            'description' => 'Clean the lobby floor every morning',
            'location' => 'Lobby',
            'location_id' => $this->location->id,
            'category' => TaskCategory::Cleaning,
            'priority' => TaskPriority::Medium,
            'frequency' => 'daily',
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $this->assertDatabaseHas('recurring_tasks', [
            'id' => $task->id,
            'title' => 'Daily Floor Cleaning',
            'frequency' => 'daily',
        ]);
    }

    // ── P3-TC02: shouldGenerateToday daily always true ──

    public function test_p3_tc02_should_generate_today_daily(): void
    {
        $task = RecurringTask::create([
            'title' => 'Daily Task',
            'location' => 'Lobby',
            'frequency' => 'daily',
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => true,
        ]);

        $this->assertTrue($task->shouldGenerateToday());
    }

    public function test_p3_tc02_should_generate_today_daily_already_generated(): void
    {
        $task = RecurringTask::create([
            'title' => 'Daily Task',
            'location' => 'Lobby',
            'frequency' => 'daily',
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => true,
            'last_generated_at' => now(),
        ]);

        $this->assertFalse($task->shouldGenerateToday());
    }

    // ── P3-TC03: shouldGenerateToday weekly checks day ──

    public function test_p3_tc03_should_generate_today_weekly_correct_day(): void
    {
        $today = Carbon::now()->dayOfWeek;

        $task = RecurringTask::create([
            'title' => 'Weekly Task',
            'location' => 'Lobby',
            'frequency' => 'weekly',
            'day_of_week' => $today,
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => true,
        ]);

        $this->assertTrue($task->shouldGenerateToday());
    }

    public function test_p3_tc03_should_generate_today_weekly_wrong_day(): void
    {
        $wrongDay = (Carbon::now()->dayOfWeek + 1) % 7;

        $task = RecurringTask::create([
            'title' => 'Weekly Task',
            'location' => 'Lobby',
            'frequency' => 'weekly',
            'day_of_week' => $wrongDay,
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => true,
        ]);

        $this->assertFalse($task->shouldGenerateToday());
    }

    // ── P3-TC04: shouldGenerateToday monthly checks day ──

    public function test_p3_tc04_should_generate_today_monthly_correct_day(): void
    {
        $today = Carbon::now()->day;

        $task = RecurringTask::create([
            'title' => 'Monthly Task',
            'location' => 'Lobby',
            'frequency' => 'monthly',
            'day_of_month' => $today,
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => true,
        ]);

        $this->assertTrue($task->shouldGenerateToday());
    }

    public function test_p3_tc04_should_generate_today_monthly_wrong_day(): void
    {
        $wrongDay = (Carbon::now()->day % 28) + 1;

        $task = RecurringTask::create([
            'title' => 'Monthly Task',
            'location' => 'Lobby',
            'frequency' => 'monthly',
            'day_of_month' => $wrongDay,
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => true,
        ]);

        $this->assertFalse($task->shouldGenerateToday());
    }

    // ── P3-TC05: shouldGenerateToday inactive returns false ──

    public function test_p3_tc05_should_generate_today_inactive(): void
    {
        $task = RecurringTask::create([
            'title' => 'Paused Task',
            'location' => 'Lobby',
            'frequency' => 'daily',
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => false,
        ]);

        $this->assertFalse($task->shouldGenerateToday());
    }

    // ── P3-TC06: generateTask creates Task + updates last_generated_at ──

    public function test_p3_tc06_generate_task_creates_task(): void
    {
        $recurring = RecurringTask::create([
            'title' => 'Floor Cleaning',
            'description' => 'Clean lobby floor',
            'location' => 'Lobby',
            'location_id' => $this->location->id,
            'category' => TaskCategory::Cleaning,
            'priority' => TaskPriority::High,
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => true,
        ]);

        $task = $recurring->generateTask();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Floor Cleaning',
            'recurring_task_id' => $recurring->id,
            'status' => 'pending',
            'assigned_to' => $this->staff->id,
        ]);

        $this->assertNotNull($recurring->fresh()->last_generated_at);
    }

    // ── P3-TC07: Artisan command generates daily tasks ──

    public function test_p3_tc07_artisan_command_generates_daily_tasks(): void
    {
        RecurringTask::create([
            'title' => 'Daily Cleaning',
            'location' => 'Lobby',
            'frequency' => 'daily',
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => true,
        ]);

        $this->artisan('tasks:generate-recurring')
            ->expectsOutputToContain('Generated')
            ->assertExitCode(0);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Daily Cleaning',
            'recurring_task_id' => RecurringTask::first()->id,
        ]);
    }

    public function test_p3_tc07_artisan_command_no_tasks_when_not_due(): void
    {
        RecurringTask::create([
            'title' => 'Weekly Task',
            'location' => 'Lobby',
            'frequency' => 'weekly',
            'day_of_week' => (Carbon::now()->dayOfWeek + 1) % 7, // Tomorrow
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => true,
        ]);

        $this->artisan('tasks:generate-recurring')
            ->expectsOutputToContain('Done. Generated 0')
            ->assertExitCode(0);
    }

    // ── P3-TC08: Supervisor can create recurring task via Livewire ──

    public function test_p3_tc08_supervisor_can_create_recurring_task(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\RecurringTaskManagement::class)
            ->set('title', 'Daily Mopping')
            ->set('locationId', $this->location->id)
            ->set('assignedTo', $this->staff->id)
            ->set('frequency', 'daily')
            ->call('saveTask');

        $this->assertDatabaseHas('recurring_tasks', [
            'title' => 'Daily Mopping',
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    // ── P3-TC09: Supervisor cannot create without required fields ──

    public function test_p3_tc09_supervisor_cannot_create_without_required_fields(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\RecurringTaskManagement::class)
            ->set('title', '')
            ->set('locationId', '')
            ->set('assignedTo', '')
            ->call('saveTask')
            ->assertHasErrors(['title', 'locationId', 'assignedTo']);
    }

    // ── P3-TC10: Supervisor can deactivate recurring task ──

    public function test_p3_tc10_supervisor_can_deactivate_recurring_task(): void
    {
        $task = RecurringTask::create([
            'title' => 'To Deactivate',
            'location' => 'Lobby',
            'frequency' => 'daily',
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\RecurringTaskManagement::class)
            ->call('confirmDelete', $task->id)
            ->call('deleteTask');

        $this->assertDatabaseHas('recurring_tasks', [
            'id' => $task->id,
            'is_active' => false,
        ]);
    }

    // ── P3-TC11: Non-supervisor cannot access recurring tasks ──

    public function test_p3_tc11_staff_cannot_access_recurring_tasks(): void
    {
        $response = $this->actingAs($this->staff)->get(route('supervisor.recurring-tasks'));
        $response->assertForbidden();
    }

    public function test_p3_tc11_admin_cannot_access_recurring_tasks(): void
    {
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->get(route('supervisor.recurring-tasks'));
        $response->assertForbidden();
    }

    // ── P3-TC12: Relationships work correctly ──

    public function test_p3_tc12_recurring_task_has_assignee(): void
    {
        $task = RecurringTask::create([
            'title' => 'Assigned Task',
            'location' => 'Lobby',
            'frequency' => 'daily',
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $this->assertNotNull($task->assignee);
        $this->assertEquals($this->staff->id, $task->assignee->id);
    }

    public function test_p3_tc12_recurring_task_has_location(): void
    {
        $task = RecurringTask::create([
            'title' => 'Located Task',
            'location' => 'Lobby',
            'location_id' => $this->location->id,
            'frequency' => 'daily',
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $this->assertEquals($this->location->id, $task->location_id);
        $this->assertEquals('Lobby', $task->location);
    }

    // ── Edge Case: Toggle active status ──

    public function test_p3_edge_toggle_active_status(): void
    {
        $task = RecurringTask::create([
            'title' => 'Toggle Task',
            'location' => 'Lobby',
            'frequency' => 'daily',
            'assigned_to' => $this->staff->id,
            'supervisor_id' => $this->supervisor->id,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\RecurringTaskManagement::class)
            ->call('toggleActive', $task->id);

        $this->assertFalse($task->fresh()->is_active);

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\RecurringTaskManagement::class)
            ->call('toggleActive', $task->id);

        $this->assertTrue($task->fresh()->is_active);
    }
}
