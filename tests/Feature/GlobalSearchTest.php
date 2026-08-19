<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $supervisor;
    private User $staff;
    private User $otherSupervisor;
    private User $otherStaff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['is_active' => true]);
        $this->supervisor = User::factory()->supervisor()->create(['is_active' => true]);
        $this->staff = User::factory()->staff()->create([
            'is_active' => true,
            'supervisor_id' => $this->supervisor->id,
        ]);
        $this->otherSupervisor = User::factory()->supervisor()->create(['is_active' => true]);
        $this->otherStaff = User::factory()->staff()->create([
            'is_active' => true,
            'supervisor_id' => $this->otherSupervisor->id,
        ]);

        $this->location = Location::create([
            'name' => 'Conference Room A',
            'building' => 'Main Building',
            'floor' => '1st Floor',
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'supervisor_id' => $this->supervisor->id,
            'created_by' => $this->admin->id,
        ]);

        $this->otherLocation = Location::create([
            'name' => 'Server Room B',
            'building' => 'IT Building',
            'floor' => '2nd Floor',
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'supervisor_id' => $this->otherSupervisor->id,
            'created_by' => $this->admin->id,
        ]);

        $this->task = Task::create([
            'title' => 'Clean Conference Room',
            'location' => 'Conference Room A',
            'location_id' => $this->location->id,
            'category' => 'cleaning',
            'priority' => 'medium',
            'status' => 'pending',
            'supervisor_id' => $this->supervisor->id,
            'assigned_to' => $this->staff->id,
        ]);

        $this->otherTask = Task::create([
            'title' => 'Maintain Server Room',
            'location' => 'Server Room B',
            'location_id' => $this->otherLocation->id,
            'category' => 'maintenance',
            'priority' => 'high',
            'status' => 'in_progress',
            'supervisor_id' => $this->otherSupervisor->id,
            'assigned_to' => $this->otherStaff->id,
        ]);

        $this->issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Broken window in conference room',
            'tracking_code' => 'SP-TEST12',
            'status' => 'reported',
        ]);

        $this->otherIssue = Issue::create([
            'location_id' => $this->otherLocation->id,
            'description' => 'Faulty server rack in server room',
            'tracking_code' => 'SP-OTH34',
            'status' => 'assigned',
            'assigned_to' => $this->otherStaff->id,
        ]);
    }

    // ── P2-TC01: Search with < 2 chars returns nothing ──

    public function test_p2_tc01_search_with_less_than_2_chars_returns_nothing(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', '')
            ->assertSet('results', []);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'a')
            ->assertSet('results', []);
    }

    // ── P2-TC02: Search finds tasks by title ──

    public function test_p2_tc02_search_finds_tasks_by_title(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Conference')
            ->assertSee('Clean Conference Room')
            ->assertSee('task');
    }

    // ── P2-TC03: Search finds issues by description ──

    public function test_p2_tc03_search_finds_issues_by_description(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Broken window')
            ->assertSee('SP-TEST12')
            ->assertSee('issue');
    }

    // ── P2-TC04: Search finds issues by tracking code ──

    public function test_p2_tc04_search_finds_issues_by_tracking_code(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'SP-TEST')
            ->assertSee('SP-TEST12');
    }

    // ── P2-TC05: Search finds locations by name ──

    public function test_p2_tc05_search_finds_locations_by_name(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Conference')
            ->assertSee('Conference Room A')
            ->assertSee('location');
    }

    // ── P2-TC06: Search finds users (admin only) ──

    public function test_p2_tc06_admin_search_finds_users(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', $this->supervisor->name)
            ->assertSee($this->supervisor->name)
            ->assertSee('user');
    }

    public function test_p2_tc06_non_admin_doesnt_search_users(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', $this->admin->name)
            ->assertDontSee('user');
    }

    // ── P2-TC07: Search results limited to 5 per type ──

    public function test_p2_tc07_search_results_limited_per_type(): void
    {
        // Create 7 tasks
        for ($i = 0; $i < 7; $i++) {
            Task::create([
                'title' => "Test Task {$i}",
                'location' => 'Test Location',
                'location_id' => $this->location->id,
                'category' => 'cleaning',
                'priority' => 'low',
                'status' => 'pending',
                'supervisor_id' => $this->supervisor->id,
                'assigned_to' => $this->staff->id,
            ]);
        }

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Test Task');

        $component = Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Test Task');

        // Should have max 5 task results
        $taskResults = collect($component->get('results'))->where('type', 'task');
        $this->assertLessThanOrEqual(5, $taskResults->count());
    }

    // ── P2-TC08: Close search clears query ──

    public function test_p2_tc08_close_search_clears_query(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'something')
            ->call('closeSearch')
            ->assertSet('query', '')
            ->assertSet('results', []);
    }

    // ── P2-TC09: Search with no results shows empty state ──

    public function test_p2_tc09_search_with_no_results(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'xyznonexistent')
            ->assertSet('results', []);
    }

    // ── P2-TC10: Search is case insensitive ──

    public function test_p2_tc10_search_is_case_insensitive(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'conference')
            ->assertSee('Conference Room A');
    }

    // ── Edge Case: Special characters in search ──

    public function test_p2_edge_special_characters_in_search(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', "'; DROP TABLE tasks; --")
            ->assertSet('results', []);
    }

    // ── Edge Case: Search by location building ──

    public function test_p2_edge_search_by_building(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Main Building')
            ->assertSee('Conference Room A');
    }

    // ── P2-TC11: Admin sees all tasks ──

    public function test_p2_tc11_admin_sees_all_tasks(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Clean Conference')
            ->assertSee('Clean Conference Room');

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Maintain Server')
            ->assertSee('Maintain Server Room');
    }

    // ── P2-TC12: Admin sees all issues ──

    public function test_p2_tc12_admin_sees_all_issues(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Broken window')
            ->assertSee('SP-TEST12');

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Faulty server')
            ->assertSee('SP-OTH34');
    }

    // ── P2-TC13: Admin sees all locations ──

    public function test_p2_tc13_admin_sees_all_locations(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Conference Room')
            ->assertSee('Conference Room A');

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Server Room')
            ->assertSee('Server Room B');
    }

    // ── P2-TC14: Supervisor only sees their own tasks ──

    public function test_p2_tc14_supervisor_only_sees_own_tasks(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Clean Conference')
            ->assertSee('Clean Conference Room');

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Maintain Server')
            ->assertDontSee('Maintain Server Room');
    }

    // ── P2-TC15: Supervisor only sees issues at their locations ──

    public function test_p2_tc15_supervisor_only_sees_own_location_issues(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Broken window')
            ->assertSee('SP-TEST12');

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Faulty server')
            ->assertDontSee('SP-OTH34');
    }

    // ── P2-TC16: Supervisor only sees their own locations ──

    public function test_p2_tc16_supervisor_only_sees_own_locations(): void
    {
        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Conference Room')
            ->assertSee('Conference Room A');

        Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Server Room')
            ->assertDontSee('Server Room B');
    }

    // ── P2-TC17: Staff only sees tasks assigned to them ──

    public function test_p2_tc17_staff_only_sees_own_tasks(): void
    {
        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Clean Conference')
            ->assertSee('Clean Conference Room');

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Maintain Server')
            ->assertDontSee('Maintain Server Room');
    }

    // ── P2-TC18: Staff only sees issues assigned to them ──

    public function test_p2_tc18_staff_only_sees_own_issues(): void
    {
        $this->issue->update(['assigned_to' => $this->staff->id]);

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Broken window')
            ->assertSee('SP-TEST12');

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Faulty server')
            ->assertDontSee('SP-OTH34');
    }

    // ── P2-TC19: Staff only sees locations from their assigned tasks ──

    public function test_p2_tc19_staff_sees_locations_from_own_tasks(): void
    {
        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Conference Room')
            ->assertSee('Conference Room A');

        Livewire::actingAs($this->staff)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Server Room')
            ->assertDontSee('Server Room B');
    }

    // ── P2-TC20: Staff cannot see users ──

    public function test_p2_tc20_staff_cannot_see_users(): void
    {
        $component = Livewire::actingAs($this->staff)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', $this->admin->name);

        $userResults = collect($component->get('results'))->where('type', 'user');
        $this->assertCount(0, $userResults);
    }

    // ── P2-TC21: Supervisor cannot see users ──

    public function test_p2_tc21_supervisor_cannot_see_users(): void
    {
        $component = Livewire::actingAs($this->supervisor)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', $this->admin->name);

        $userResults = collect($component->get('results'))->where('type', 'user');
        $this->assertCount(0, $userResults);
    }
}
