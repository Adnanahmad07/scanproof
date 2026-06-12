<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupervisorWorkerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;
    protected User $admin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->supervisor = User::factory()->supervisor()->create();
        $this->staff = User::factory()->staff()->create([
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    public function test_supervisor_can_access_workers_page(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('supervisor.workers'));

        $response->assertStatus(200);
        $response->assertSee('My Workers');
    }

    public function test_unauthenticated_user_cannot_access_workers_page(): void
    {
        $response = $this->get(route('supervisor.workers'));

        $response->assertRedirect('/login');
    }

    public function test_non_supervisor_cannot_access_workers_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('supervisor.workers'));

        $response->assertForbidden();
    }

    public function test_staff_cannot_access_workers_page(): void
    {
        $response = $this->actingAs($this->staff)->get(route('supervisor.workers'));

        $response->assertForbidden();
    }

    public function test_supervisor_can_add_worker(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\WorkerManagement::class)
            ->set('workerName', 'New Worker')
            ->set('workerEmail', 'newworker@example.com')
            ->set('workerPassword', 'password123')
            ->call('addWorker');

        $this->assertDatabaseHas('users', [
            'name' => 'New Worker',
            'email' => 'newworker@example.com',
            'role' => UserRole::Staff,
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    public function test_supervisor_cannot_add_worker_with_existing_email(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\WorkerManagement::class)
            ->set('workerName', 'Duplicate Worker')
            ->set('workerEmail', $this->staff->email)
            ->set('workerPassword', 'password123')
            ->call('addWorker')
            ->assertHasErrors(['workerEmail']);
    }

    public function test_supervisor_cannot_add_worker_with_invalid_data(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\WorkerManagement::class)
            ->set('workerName', '')
            ->set('workerEmail', 'not-an-email')
            ->set('workerPassword', 'short')
            ->call('addWorker')
            ->assertHasErrors(['workerName', 'workerEmail', 'workerPassword']);
    }

    public function test_supervisor_sees_only_own_workers(): void
    {
        $otherSupervisor = User::factory()->supervisor()->create();
        $otherWorker = User::factory()->staff()->create([
            'supervisor_id' => $otherSupervisor->id,
        ]);

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\WorkerManagement::class)
            ->assertSee($this->staff->name)
            ->assertDontSee($otherWorker->name);
    }

    public function test_supervisor_can_remove_worker(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\WorkerManagement::class)
            ->call('confirmDelete', $this->staff->id)
            ->call('deleteWorker');

        $this->assertDatabaseHas('users', [
            'id' => $this->staff->id,
            'supervisor_id' => null,
        ]);
    }

    public function test_new_worker_appears_in_supervisor_list(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\Supervisor\WorkerManagement::class)
            ->set('workerName', 'Listed Worker')
            ->set('workerEmail', 'listed@example.com')
            ->set('workerPassword', 'password123')
            ->call('addWorker')
            ->assertSee('Listed Worker');
    }
}
