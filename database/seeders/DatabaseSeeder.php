<?php

namespace Database\Seeders;

use App\Enums\TaskCategory;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Issue;
use App\Models\Location;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@scanproof.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);

        // Create Supervisors
        $supervisors = [];
        for ($i = 1; $i <= 3; $i++) {
            $supervisors[] = User::factory()->create([
                'name' => "Supervisor {$i}",
                'email' => "supervisor{$i}@scanproof.com",
                'password' => Hash::make('password'),
                'role' => UserRole::Supervisor,
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
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        // Create Issues
        $issueData = [
            ['description' => 'Broken window in conference room A, glass cracked near the handle. Needs immediate repair.', 'status' => 'reported'],
            ['description' => 'Water leak from ceiling in lobby area. Water dripping near the reception desk.', 'status' => 'assigned'],
            ['description' => 'Air conditioning not working on 2nd floor. Temperature is very uncomfortable.', 'status' => 'in_progress'],
            ['description' => 'Broken door handle on server room. Security concern.', 'status' => 'resolved'],
            ['description' => 'Flickering lights in parking area. Safety hazard for staff at night.', 'status' => 'reported'],
            ['description' => 'Broken tile in kitchen area. Trip hazard.', 'status' => 'resolved'],
            ['description' => 'Printer not working on 3rd floor. Affecting productivity.', 'status' => 'assigned'],
        ];

        foreach ($issueData as $i => $data) {
            $location = $locations[array_rand($locations)];
            $createdAt = $now->copy()->subDays(rand(1, 14))->subHours(rand(0, 23));

            Issue::create([
                'tracking_code' => Issue::generateTrackingCode(),
                'location_id' => $location->id,
                'description' => $data['description'],
                'status' => $data['status'],
                'reported_by' => $workers[array_rand($workers)]->id,
                'assigned_to' => $data['status'] !== 'reported' ? $workers[array_rand($workers)]->id : null,
                'resolved_at' => $data['status'] === 'resolved' ? $createdAt->copy()->addHours(rand(1, 48)) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('Database seeded with sample data!');
    }
}
