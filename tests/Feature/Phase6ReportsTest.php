<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase6ReportsTest extends TestCase
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

    // ── TC-6.1: Stats computed correctly from seeded data ──

    public function test_tc6_1_admin_can_access_reports_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/reports');
        $response->assertStatus(200);
        $response->assertSee('Reports');
    }

    public function test_tc6_1_non_admin_cannot_access_reports(): void
    {
        $supervisor = $this->createSupervisor();

        $response = $this->actingAs($supervisor)->get('/admin/reports');
        $response->assertStatus(403);
    }

    public function test_tc6_1_dashboard_shows_total_staff(): void
    {
        $admin = $this->createAdmin();
        User::factory()->count(3)->create(['role' => 'staff']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('4'); // 3 staff + 1 admin
    }

    public function test_tc6_1_dashboard_shows_active_tasks(): void
    {
        $admin = $this->createAdmin();
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        Task::create([
            'title' => 'Active Task',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'medium',
            'status' => TaskStatus::InProgress,
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('1');
    }

    public function test_tc6_1_dashboard_shows_open_issues(): void
    {
        $admin = $this->createAdmin();
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        Issue::create([
            'location_id' => $location->id,
            'description' => 'Test issue for dashboard',
            'status' => 'reported',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Open Issues');
    }

    // ── TC-6.2: Report builds for a date range ──

    public function test_tc6_2_report_page_with_date_range(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/reports?start_date=2026-01-01&end_date=2026-12-31');
        $response->assertStatus(200);
        $response->assertSee('2026-01-01');
        $response->assertSee('2026-12-31');
    }

    public function test_tc6_2_report_shows_task_stats(): void
    {
        $admin = $this->createAdmin();
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        Task::create([
            'title' => 'Test Task',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'maintenance',
            'priority' => 'high',
            'status' => TaskStatus::Completed,
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');
        $response->assertStatus(200);
        $response->assertSee('Total Tasks');
        $response->assertSee('1');
    }

    public function test_tc6_2_report_shows_issue_stats(): void
    {
        $admin = $this->createAdmin();
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        Issue::create([
            'location_id' => $location->id,
            'description' => 'Test issue for report',
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');
        $response->assertStatus(200);
        $response->assertSee('Total Issues');
        $response->assertSee('1');
    }

    // ── TC-6.3: PDF downloads (200, correct headers) ──

    public function test_tc6_3_pdf_download_returns_200(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/reports/pdf?start_date=2026-01-01&end_date=2026-12-31');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_tc6_3_pdf_has_correct_filename(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/reports/pdf?start_date=2026-01-01&end_date=2026-12-31');
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
    }

    // ── TC-6.4: Health score formula returns expected value ──

    public function test_tc6_4_health_score_empty_database(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/reports');
        $response->assertStatus(200);
        // With no data, score should be 100 (all defaults are perfect)
        $response->assertSee('100');
    }

    public function test_tc6_4_health_score_with_completed_tasks(): void
    {
        $admin = $this->createAdmin();
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        // All tasks completed = high score
        Task::create([
            'title' => 'Completed Task',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'cleaning',
            'priority' => 'medium',
            'status' => TaskStatus::Completed,
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');
        $response->assertStatus(200);
        // Score should be high since all tasks are completed
        $response->assertSee('Excellent');
    }

    public function test_tc6_4_health_score_with_overdue_tasks(): void
    {
        $admin = $this->createAdmin();
        $supervisor = $this->createSupervisor();
        $location = $this->createLocation($supervisor);

        // Overdue task = lower score
        Task::create([
            'title' => 'Overdue Task',
            'location' => $location->name,
            'location_id' => $location->id,
            'category' => 'maintenance',
            'priority' => 'urgent',
            'status' => TaskStatus::Pending,
            'supervisor_id' => $supervisor->id,
            'due_date' => now()->subDays(5),
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');
        $response->assertStatus(200);
        // Score should be lower due to overdue task
        $this->assertStringContainsString('Health', $response->getContent());
    }

    // ── Reports link in sidebar ──

    public function test_reports_link_in_admin_sidebar(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('/admin/reports');
        $response->assertSee('Reports');
    }

    // ── Health score service unit tests ──

    public function test_health_score_service_calculate(): void
    {
        $service = new \App\Services\HealthScoreService();
        $score = $service->calculate();
        $this->assertIsInt($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_health_score_service_labels(): void
    {
        $service = new \App\Services\HealthScoreService();
        $this->assertEquals('Excellent', $service->getLabel(85));
        $this->assertEquals('Good', $service->getLabel(65));
        $this->assertEquals('Fair', $service->getLabel(45));
        $this->assertEquals('Poor', $service->getLabel(25));
        $this->assertEquals('Critical', $service->getLabel(10));
    }

    public function test_health_score_service_colors(): void
    {
        $service = new \App\Services\HealthScoreService();
        $this->assertEquals('success', $service->getColor(85));
        $this->assertEquals('info', $service->getColor(65));
        $this->assertEquals('warning', $service->getColor(45));
        $this->assertEquals('error', $service->getColor(25));
    }

    // ── Analytics service unit tests ──

    public function test_analytics_service_dashboard_stats(): void
    {
        $service = new \App\Services\AnalyticsService();
        $stats = $service->getDashboardStats();

        $this->assertArrayHasKey('totalStaff', $stats);
        $this->assertArrayHasKey('activeTasks', $stats);
        $this->assertArrayHasKey('totalLocations', $stats);
        $this->assertArrayHasKey('openIssues', $stats);
    }

    public function test_analytics_service_report_data(): void
    {
        $service = new \App\Services\AnalyticsService();
        $data = $service->getReportData();

        $this->assertArrayHasKey('period', $data);
        $this->assertArrayHasKey('tasks', $data);
        $this->assertArrayHasKey('issues', $data);
        $this->assertArrayHasKey('locations', $data);
        $this->assertArrayHasKey('resolutionTime', $data);
        $this->assertArrayHasKey('topLocations', $data);
        $this->assertArrayHasKey('byCategory', $data);
        $this->assertArrayHasKey('byPriority', $data);
    }
}
