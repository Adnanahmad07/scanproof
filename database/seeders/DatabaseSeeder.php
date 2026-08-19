<?php

namespace Database\Seeders;

use App\Enums\TaskCategory;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Issue;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Organization
        $organization = Organization::create([
            'name' => 'ScanProof Demo Hospital',
            'slug' => 'scanproof-demo-hospital',
        ]);

        // Create Admin
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@scanproof.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'organization_id' => $organization->id,
        ]);

        // Create Supervisors
        $supervisors = [];
        for ($i = 1; $i <= 3; $i++) {
            $supervisors[] = User::factory()->create([
                'name' => "Supervisor {$i}",
                'email' => "supervisor{$i}@scanproof.com",
                'password' => Hash::make('password'),
                'role' => UserRole::Supervisor,
                'organization_id' => $organization->id,
            ]);
        }

        // Create Staff (workers under supervisors)
        $workers = [];
        foreach ($supervisors as $si => $supervisor) {
            for ($i = 1; $i <= 4; $i++) {
                $workers[] = User::factory()->create([
                    'name' => "Worker " . (($si * 4) + $i),
                    'email' => "worker" . (($si * 4) + $i) . "@scanproof.com",
                    'password' => Hash::make('password'),
                    'role' => UserRole::Staff,
                    'supervisor_id' => $supervisor->id,
                    'organization_id' => $organization->id,
                ]);
            }
        }

        // Create Locations
        $locations = [];
        $buildings = ['Main Building', 'Warehouse', 'Office Tower'];
        $floors = ['Ground Floor', '1st Floor', '2nd Floor', '3rd Floor'];
        $rooms = ['Lobby', 'Conference Room A', 'Conference Room B', 'Kitchen', 'Restroom', 'Server Room', 'Parking Area', 'Reception'];

        foreach ($buildings as $bi => $building) {
            foreach (array_slice($floors, 0, 2 + $bi) as $fi => $floor) {
                foreach (array_slice($rooms, 0, 3 + $fi) as $room) {
                    $locations[] = Location::create([
                        'name' => $room,
                        'building' => $building,
                        'floor' => $floor,
                        'supervisor_id' => $supervisors[$bi % count($supervisors)]->id,
                        'created_by' => $admin->id,
                        'organization_id' => $organization->id,
                    ]);
                }
            }
        }

        // Create Tasks (mix of statuses)
        $taskData = [
            ['title' => 'Morning Floor Cleaning', 'category' => 'cleaning', 'priority' => 'medium'],
            ['title' => 'Restroom Deep Clean', 'category' => 'cleaning', 'priority' => 'high'],
            ['title' => 'AC Filter Replacement', 'category' => 'maintenance', 'priority' => 'high'],
            ['title' => 'Fire Extinguisher Check', 'category' => 'inspection', 'priority' => 'urgent'],
            ['title' => 'Window Washing', 'category' => 'cleaning', 'priority' => 'low'],
            ['title' => 'Light Bulb Replacement', 'category' => 'maintenance', 'priority' => 'medium'],
            ['title' => 'Carpet Vacuuming', 'category' => 'cleaning', 'priority' => 'medium'],
            ['title' => 'Desk Sanitization', 'category' => 'cleaning', 'priority' => 'high'],
            ['title' => 'Electrical Panel Inspection', 'category' => 'inspection', 'priority' => 'urgent'],
            ['title' => 'Plumbing Check', 'category' => 'maintenance', 'priority' => 'medium'],
            ['title' => 'Garden Maintenance', 'category' => 'other', 'priority' => 'low'],
            ['title' => 'Elevator Service', 'category' => 'maintenance', 'priority' => 'high'],
        ];

        $statuses = [TaskStatus::Pending, TaskStatus::InProgress, TaskStatus::Completed, TaskStatus::Verified];
        $now = Carbon::now();

        foreach ($taskData as $i => $data) {
            $worker = $workers[array_rand($workers)];
            $supervisor = $supervisors[array_rand($supervisors)];
            $location = $locations[array_rand($locations)];
            $status = $statuses[$i % count($statuses)];

            $createdAt = $now->copy()->subDays(rand(1, 30))->subHours(rand(0, 12));

            Task::create([
                'title' => $data['title'],
                'description' => "Detailed description for {$data['title']}. This task needs to be completed with care.",
                'location' => $location->name,
                'location_id' => $location->id,
                'category' => $data['category'],
                'priority' => $data['priority'],
                'status' => $status,
                'due_date' => $now->copy()->addDays(rand(-2, 7))->toDateString(),
                'supervisor_id' => $supervisor->id,
                'assigned_to' => $worker->id,
                'organization_id' => $organization->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        // Create Issues
        $issueData = [
            ['description' => 'Broken air conditioning unit in conference room. Not cooling properly.', 'status' => 'reported'],
            ['description' => 'Water leak near the elevator on ground floor. Needs urgent attention.', 'status' => 'assigned'],
            ['description' => 'Lights flickering in the main lobby. Possible wiring issue.', 'status' => 'in_progress'],
            ['description' => 'Fire alarm panel showing error code E-42. Needs inspection.', 'status' => 'resolved'],
            ['description' => 'Broken window in office 204. Glass cracked during cleaning.', 'status' => 'reported'],
            ['description' => 'Clogged drain in kitchen area. Water backing up.', 'status' => 'assigned'],
            ['description' => 'Parking lot pothole needs repair. Safety hazard for vehicles.', 'status' => 'reported'],
            ['description' => 'AC unit making unusual noise in server room. May need filter change.', 'status' => 'in_progress'],
        ];

        foreach ($issueData as $i => $data) {
            $location = $locations[array_rand($locations)];
            $worker = $workers[array_rand($workers)];

            $issue = Issue::create([
                'tracking_code' => Issue::generateTrackingCode(),
                'location_id' => $location->id,
                'description' => $data['description'],
                'status' => $data['status'],
                'assigned_to' => $data['status'] !== 'reported' ? $worker->id : null,
                'organization_id' => $organization->id,
                'created_at' => $now->copy()->subDays(rand(1, 14)),
            ]);
        }
    }
}
