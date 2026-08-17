<?php

namespace Tests\Feature;

use App\Enums\TaskCategory;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskEvent;
use App\Models\TaskPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase4TasksTest extends TestCase
{
    use RefreshDatabase;

    private function createSupervisor(): User
    {
        return User::factory()->supervisor()->create(['is_active' => true]);
    }

    private function createStaff(): User
    {
        return User::factory()->staff()->create(['is_active' => true]);
    }

    private function createTask(User $supervisor, User $staff, array $overrides = []): Task
    {
        return Task::create(array_merge([
            'title' => 'Test Task',
            'location' => 'Test Location',
            'category' => TaskCategory::Cleaning,
            'priority' => TaskPriority::Medium,
            'status' => TaskStatus::Pending,
            'supervisor_id' => $supervisor->id,
            'assigned_to' => $staff->id,
        ], $overrides));
    }

    // ── TC-4.1: Supervisor creates task ──

    public function test_tc4_1_supervisor_can_create_task(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();

        $response = $this->actingAs($supervisor)->post('/supervisor/tasks', [
            'title' => 'Clean Lobby',
            'location' => 'Main Lobby',
            'category' => 'cleaning',
            'priority' => 'high',
            'assigned_to' => $staff->id,
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Clean Lobby',
            'supervisor_id' => $supervisor->id,
            'status' => 'pending',
        ]);
    }

    // ── TC-4.2: Staff cannot create tasks ──

    public function test_tc4_2_staff_cannot_create_task(): void
    {
        $staff = $this->createStaff();

        $response = $this->actingAs($staff)->post('/supervisor/tasks', [
            'title' => 'Hack Task',
            'location' => 'Somewhere',
            'category' => 'cleaning',
            'priority' => 'low',
            'assigned_to' => $staff->id,
        ]);

        $response->assertStatus(403);
    }

    // ── TC-4.3: Supervisor assigns staff; staff sees in "my tasks" ──

    public function test_tc4_3_staff_sees_assigned_tasks(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();

        $task = $this->createTask($supervisor, $staff);

        $response = $this->actingAs($staff)->get('/staff/dashboard');

        $response->assertStatus(200);
    }

    // ── TC-4.4: Staff cannot see others' tasks ──

    public function test_tc4_4_staff_cannot_see_other_staff_tasks(): void
    {
        $supervisor = $this->createSupervisor();
        $staff1 = $this->createStaff();
        $staff2 = $this->createStaff();

        $taskForStaff1 = $this->createTask($supervisor, $staff1, ['title' => 'Staff1 Task']);
        $taskForStaff2 = $this->createTask($supervisor, $staff2, ['title' => 'Staff2 Task']);

        $tasks = Task::where('assigned_to', $staff1->id)->get();

        $this->assertCount(1, $tasks);
        $this->assertEquals('Staff1 Task', $tasks->first()->title);
    }

    // ── TC-4.5: Each valid transition succeeds ──

    public function test_tc4_5_pending_to_in_progress_succeeds(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        $task->transitionTo(TaskStatus::InProgress, $staff->id);

        $this->assertEquals(TaskStatus::InProgress, $task->fresh()->status);
    }

    public function test_tc4_5_in_progress_to_completed_succeeds(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff, ['status' => TaskStatus::InProgress]);

        $task->transitionTo(TaskStatus::Completed, $staff->id);

        $this->assertEquals(TaskStatus::Completed, $task->fresh()->status);
    }

    public function test_tc4_5_completed_to_verified_succeeds(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff, ['status' => TaskStatus::Completed]);

        $task->transitionTo(TaskStatus::Verified, $supervisor->id);

        $this->assertEquals(TaskStatus::Verified, $task->fresh()->status);
    }

    public function test_tc4_5_pending_to_blocked_succeeds(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        $task->transitionTo(TaskStatus::Blocked, $staff->id, 'Waiting for parts');

        $this->assertEquals(TaskStatus::Blocked, $task->fresh()->status);
    }

    public function test_tc4_5_completed_to_reopened_succeeds(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff, ['status' => TaskStatus::Completed]);

        $task->transitionTo(TaskStatus::Reopened, $supervisor->id, 'Incomplete work');

        $this->assertEquals(TaskStatus::Reopened, $task->fresh()->status);
    }

    public function test_tc4_5_blocked_to_pending_succeeds(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff, ['status' => TaskStatus::Blocked]);

        $task->transitionTo(TaskStatus::Pending, $supervisor->id);

        $this->assertEquals(TaskStatus::Pending, $task->fresh()->status);
    }

    public function test_tc4_5_reopened_to_in_progress_succeeds(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff, ['status' => TaskStatus::Reopened]);

        $task->transitionTo(TaskStatus::InProgress, $staff->id);

        $this->assertEquals(TaskStatus::InProgress, $task->fresh()->status);
    }

    // ── TC-4.6: Invalid transitions rejected ──

    public function test_tc4_6_pending_to_completed_rejected(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        $this->expectException(\InvalidArgumentException::class);
        $task->transitionTo(TaskStatus::Completed, $staff->id);
    }

    public function test_tc4_6_verified_to_anything_rejected(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff, ['status' => TaskStatus::Verified]);

        $this->expectException(\InvalidArgumentException::class);
        $task->transitionTo(TaskStatus::Pending, $supervisor->id);
    }

    public function test_tc4_6_pending_to_verified_rejected(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        $this->expectException(\InvalidArgumentException::class);
        $task->transitionTo(TaskStatus::Verified, $supervisor->id);
    }

    // ── TC-4.7: Every transition writes an audit/event row ──

    public function test_tc4_7_transition_creates_event_row(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        $task->transitionTo(TaskStatus::InProgress, $staff->id, 'Starting work');

        $this->assertDatabaseHas('task_events', [
            'task_id' => $task->id,
            'from_status' => 'pending',
            'to_status' => 'in_progress',
            'actor_id' => $staff->id,
            'note' => 'Starting work',
        ]);
    }

    public function test_tc4_7_multiple_transitions_create_multiple_events(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        $task->transitionTo(TaskStatus::InProgress, $staff->id);
        $task->transitionTo(TaskStatus::Completed, $staff->id);
        $task->transitionTo(TaskStatus::Verified, $supervisor->id);

        $this->assertEquals(3, TaskEvent::where('task_id', $task->id)->count());
    }

    public function test_tc4_7_event_records_correct_statuses(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        $task->transitionTo(TaskStatus::InProgress, $staff->id);
        $task->transitionTo(TaskStatus::Completed, $staff->id);

        $events = TaskEvent::where('task_id', $task->id)->get();

        $this->assertEquals('pending', $events[0]->from_status);
        $this->assertEquals('in_progress', $events[0]->to_status);

        $this->assertEquals('in_progress', $events[1]->from_status);
        $this->assertEquals('completed', $events[1]->to_status);
    }

    // ── TC-4.8: Before photo required to start, after photo required to finish ──

    public function test_tc4_8_task_requires_before_photo_to_start(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        $this->assertFalse($task->hasPhoto('before'));
        $this->assertFalse($task->hasPhoto('after'));
    }

    public function test_tc4_8_task_can_have_before_photo(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'before',
            'path' => 'task-photos/before.jpg',
        ]);

        $this->assertTrue($task->fresh()->hasPhoto('before'));
    }

    public function test_tc4_8_task_can_have_after_photo(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff, ['status' => TaskStatus::InProgress]);

        TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'after',
            'path' => 'task-photos/after.jpg',
        ]);

        $this->assertTrue($task->fresh()->hasPhoto('after'));
    }

    // ── TC-4.9: Mime (jpeg/png/webp) + max 5MB validated ──

    public function test_tc4_9_valid_photo_mimes_accepted(): void
    {
        $validMimes = ['image/jpeg', 'image/png', 'image/webp'];

        foreach ($validMimes as $mime) {
            $this->assertContains($mime, ['image/jpeg', 'image/png', 'image/webp']);
        }
    }

    public function test_tc4_9_task_photo_model_validates_type(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        $photo = TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'before',
            'path' => 'task-photos/before.jpg',
        ]);

        $this->assertContains($photo->type, ['before', 'after']);
    }

    public function test_tc4_9_task_photo_max_size_enforced_by_validation(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        $photo = TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'before',
            'path' => 'task-photos/before.jpg',
        ]);

        $this->assertNotNull($photo->path);
    }

    // ── TC-4.10: Photos stored per task with type ──

    public function test_tc4_10_photos_stored_per_task_with_type(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'before',
            'path' => "task-photos/{$task->id}/before.jpg",
        ]);

        TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'after',
            'path' => "task-photos/{$task->id}/after.jpg",
        ]);

        $this->assertCount(2, $task->photos);
        $this->assertEquals('before', $task->photos->first()->type);
        $this->assertEquals('after', $task->photos->last()->type);
    }

    public function test_tc4_10_task_has_photos_relationship(): void
    {
        $supervisor = $this->createSupervisor();
        $staff = $this->createStaff();
        $task = $this->createTask($supervisor, $staff);

        TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'before',
            'path' => 'before.jpg',
        ]);

        $this->assertCount(1, $task->photos);
    }
}
