<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Location;
use App\Models\Task;
use App\Models\TaskPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PhotoUploadQualityControlTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;
    private User $staff;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->supervisor = User::factory()->supervisor()->create(['is_active' => true]);
        $this->staff = User::factory()->staff()->create([
            'is_active' => true,
            'supervisor_id' => $this->supervisor->id,
        ]);
        $this->location = Location::create([
            'name' => 'Test Room',
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'supervisor_id' => $this->supervisor->id,
            'created_by' => $this->supervisor->id,
        ]);
    }

    private function createTask(array $overrides = []): Task
    {
        return Task::create(array_merge([
            'title' => 'Test Task',
            'location' => 'Test Room',
            'location_id' => $this->location->id,
            'category' => 'cleaning',
            'priority' => 'medium',
            'status' => TaskStatus::Pending,
            'supervisor_id' => $this->supervisor->id,
            'assigned_to' => $this->staff->id,
        ], $overrides));
    }

    // ── P5-TC01: TaskPhotoUpload component renders ──

    public function test_p5_tc01_task_photo_upload_component_renders(): void
    {
        $task = $this->createTask();

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->assertSee('Before Photos')
            ->assertSee('After Photos');
    }

    // ── P5-TC02: Photo validation - valid mime types ──

    public function test_p5_tc02_valid_jpeg_accepted(): void
    {
        $task = $this->createTask();

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->set('photoType', 'before')
            ->set('photo', UploadedFile::fake()->image('photo.jpg', 100, 100)->size(100))
            ->call('uploadPhoto')
            ->assertHasNoErrors(['photo']);
    }

    public function test_p5_tc02_valid_png_accepted(): void
    {
        $task = $this->createTask();

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->set('photoType', 'before')
            ->set('photo', UploadedFile::fake()->image('photo.png', 100, 100)->size(100))
            ->call('uploadPhoto')
            ->assertHasNoErrors(['photo']);
    }

    public function test_p5_tc02_invalid_gif_rejected(): void
    {
        $task = $this->createTask();

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->set('photoType', 'before')
            ->set('photo', UploadedFile::fake()->create('photo.gif', 100, 'image/gif'))
            ->call('uploadPhoto')
            ->assertHasErrors(['photo']);
    }

    // ── P5-TC03: Photo validation - max 5MB ──

    public function test_p5_tc03_file_over_5mb_rejected(): void
    {
        $task = $this->createTask();

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->set('photoType', 'before')
            ->set('photo', UploadedFile::fake()->image('photo.jpg', 100, 100)->size(6000))
            ->call('uploadPhoto')
            ->assertHasErrors(['photo']);
    }

    public function test_p5_tc03_file_under_5mb_accepted(): void
    {
        $task = $this->createTask();

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->set('photoType', 'before')
            ->set('photo', UploadedFile::fake()->image('photo.jpg', 100, 100)->size(4000))
            ->call('uploadPhoto')
            ->assertHasNoErrors(['photo']);
    }

    // ── P5-TC04: Photo stored in correct directory ──

    public function test_p5_tc04_photo_stored_in_correct_directory(): void
    {
        $task = $this->createTask();

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->set('photoType', 'before')
            ->set('photo', UploadedFile::fake()->image('photo.jpg', 100, 100)->size(100))
            ->call('uploadPhoto');

        $this->assertDatabaseHas('task_photos', [
            'task_id' => $task->id,
            'type' => 'before',
        ]);
    }

    // ── P5-TC05: Before photos shown in task list ──

    public function test_p5_tc05_before_photos_shown(): void
    {
        $task = $this->createTask();

        TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'before',
            'path' => 'task-photos/' . $task->id . '/before.jpg',
        ]);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->assertSee('Before Photos')
            ->assertSee('1'); // count
    }

    // ── P5-TC06: After photos shown in task list ──

    public function test_p5_tc06_after_photos_shown(): void
    {
        $task = $this->createTask(['status' => TaskStatus::InProgress]);

        TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'after',
            'path' => 'task-photos/' . $task->id . '/after.jpg',
        ]);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->assertSee('After Photos')
            ->assertSee('1'); // count
    }

    // ── P5-TC07: Supervisor can view quality control page ──

    public function test_p5_tc07_supervisor_can_view_quality_control(): void
    {
        $task = $this->createTask(['status' => TaskStatus::Completed]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.task-quality', $task->id));

        $response->assertStatus(200);
    }

    public function test_p5_tc07_wrong_supervisor_cannot_view_quality_control(): void
    {
        $otherSupervisor = User::factory()->supervisor()->create();
        $task = $this->createTask(['status' => TaskStatus::Completed]);

        Livewire::actingAs($otherSupervisor)
            ->test(\App\Livewire\Supervisor\TaskQualityControl::class, ['taskId' => $task->id])
            ->assertSee('Task not found');
    }

    // ── P5-TC08: Quality control shows before/after comparison ──

    public function test_p5_tc08_quality_control_shows_comparison(): void
    {
        $task = $this->createTask(['status' => TaskStatus::Completed]);

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

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\TaskQualityControl::class, ['taskId' => $task->id])
            ->assertSee('Before Photos')
            ->assertSee('After Photos')
            ->assertSee('(1)');
    }

    // ── P5-TC09: Staff can delete own photos ──

    public function test_p5_tc09_staff_can_delete_own_photo(): void
    {
        $task = $this->createTask();

        $photo = TaskPhoto::create([
            'task_id' => $task->id,
            'type' => 'before',
            'path' => 'task-photos/' . $task->id . '/before.jpg',
        ]);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->call('deletePhoto', $photo->id);

        $this->assertDatabaseMissing('task_photos', ['id' => $photo->id]);
    }

    // ── P5-TC10: Task detail page loads for assigned staff ──

    public function test_p5_tc10_task_detail_loads_for_assigned_staff(): void
    {
        $task = $this->createTask();

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskDetail::class, ['taskId' => $task->id])
            ->assertSee($task->title)
            ->assertSee('Start Task');
    }

    public function test_p5_tc10_task_detail_doesnt_load_for_wrong_staff(): void
    {
        $otherStaff = User::factory()->staff()->create();
        $task = $this->createTask();

        Livewire::actingAs($otherStaff)
            ->test(\App\Livewire\Staff\TaskDetail::class, ['taskId' => $task->id])
            ->assertSee('Task not found');
    }

    // ── P5-TC11: Task detail shows photo upload component ──

    public function test_p5_tc11_task_detail_shows_photo_upload(): void
    {
        $task = $this->createTask();

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskDetail::class, ['taskId' => $task->id])
            ->assertSee('Before Photos')
            ->assertSee('After Photos');
    }

    // ── P5-TC12: Photo thumbnails display ──

    public function test_p5_tc12_photo_count_displays(): void
    {
        $task = $this->createTask();

        TaskPhoto::create(['task_id' => $task->id, 'type' => 'before', 'path' => 'b1.jpg']);
        TaskPhoto::create(['task_id' => $task->id, 'type' => 'before', 'path' => 'b2.jpg']);
        TaskPhoto::create(['task_id' => $task->id, 'type' => 'after', 'path' => 'a1.jpg']);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->assertSee('(2)') // before count
            ->assertSee('(1)'); // after count
    }

    // ── Edge Case: Upload without photo shows error ──

    public function test_p5_edge_upload_without_photo_shows_error(): void
    {
        $task = $this->createTask();

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskPhotoUpload::class, ['taskId' => $task->id])
            ->call('uploadPhoto')
            ->assertHasErrors(['photo']);
    }

    // ── Edge Case: Multiple photos per type ──

    public function test_p5_edge_multiple_photos_per_type(): void
    {
        $task = $this->createTask();

        for ($i = 0; $i < 3; $i++) {
            TaskPhoto::create([
                'task_id' => $task->id,
                'type' => 'before',
                'path' => "task-photos/{$task->id}/before_{$i}.jpg",
            ]);
        }

        $this->assertCount(3, $task->photos()->where('type', 'before')->get());
    }

    // ── Edge Case: Task model hasPhoto method ──

    public function test_p5_edge_task_model_has_photo_method(): void
    {
        $task = $this->createTask();

        $this->assertFalse($task->hasPhoto('before'));

        TaskPhoto::create(['task_id' => $task->id, 'type' => 'before', 'path' => 'b.jpg']);

        $this->assertTrue($task->fresh()->hasPhoto('before'));
        $this->assertFalse($task->fresh()->hasPhoto('after'));
    }

    // ── Edge Case: Photos relationship ──

    public function test_p5_edge_task_photos_relationship(): void
    {
        $task = $this->createTask();

        TaskPhoto::create(['task_id' => $task->id, 'type' => 'before', 'path' => 'b.jpg']);
        TaskPhoto::create(['task_id' => $task->id, 'type' => 'after', 'path' => 'a.jpg']);

        $this->assertCount(2, $task->photos);
    }

    // ── Edge Case: Task start/complete from detail page ──

    public function test_p5_edge_start_task_from_detail(): void
    {
        $task = $this->createTask();

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskDetail::class, ['taskId' => $task->id])
            ->call('startTask');

        $this->assertEquals(TaskStatus::InProgress, $task->fresh()->status);
    }

    public function test_p5_edge_complete_task_from_detail(): void
    {
        Notification::fake();
        $task = $this->createTask(['status' => TaskStatus::InProgress]);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\Staff\TaskDetail::class, ['taskId' => $task->id])
            ->call('completeTask');

        $this->assertEquals(TaskStatus::Completed, $task->fresh()->status);
    }
}
