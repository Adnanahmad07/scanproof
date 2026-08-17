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

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['is_active' => true]);
        $this->supervisor = User::factory()->supervisor()->create(['is_active' => true]);
        $this->staff = User::factory()->staff()->create([
            'is_active' => true,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $this->location = Location::create([
            'name' => 'Conference Room A',
            'building' => 'Main Building',
            'floor' => '1st Floor',
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'supervisor_id' => $this->supervisor->id,
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

        $this->issue = Issue::create([
            'location_id' => $this->location->id,
            'description' => 'Broken window in conference room',
            'tracking_code' => 'SP-TEST12',
            'status' => 'reported',
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
}
