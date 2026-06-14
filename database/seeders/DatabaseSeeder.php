<?php

namespace Database\Seeders;

use App\Enums\LocationType;
use App\Enums\TaskCategory;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Location;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@scanproof.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);

        $supervisor = User::factory()->supervisor()->create([
            'name' => 'Sarah Supervisor',
            'email' => 'supervisor@scanproof.com',
            'password' => Hash::make('password'),
        ]);

        $worker1 = User::factory()->staff()->create([
            'name' => 'John Worker',
            'email' => 'worker@scanproof.com',
            'password' => Hash::make('password'),
            'supervisor_id' => $supervisor->id,
        ]);

        $worker2 = User::factory()->staff()->create([
            'name' => 'Jane Worker',
            'email' => 'jane@scanproof.com',
            'password' => Hash::make('password'),
            'supervisor_id' => $supervisor->id,
        ]);

        // --- Locations ---
        $hq = Location::create([
            'name' => 'HQ Building',
            'type' => LocationType::Building,
            'created_by' => $admin->id,
        ]);

        $ground = Location::create([
            'name' => 'Ground Floor',
            'type' => LocationType::Floor,
            'parent_id' => $hq->id,
            'building' => 'HQ Building',
            'created_by' => $admin->id,
        ]);

        $first = Location::create([
            'name' => 'First Floor',
            'type' => LocationType::Floor,
            'parent_id' => $hq->id,
            'building' => 'HQ Building',
            'created_by' => $admin->id,
        ]);

        Location::create([
            'name' => 'Main Lobby',
            'type' => LocationType::Room,
            'parent_id' => $ground->id,
            'building' => 'HQ Building',
            'floor' => 'Ground Floor',
            'created_by' => $admin->id,
        ]);

        Location::create([
            'name' => 'Conference Room A',
            'type' => LocationType::Room,
            'parent_id' => $ground->id,
            'building' => 'HQ Building',
            'floor' => 'Ground Floor',
            'created_by' => $admin->id,
        ]);

        Location::create([
            'name' => 'Server Room',
            'type' => LocationType::Room,
            'parent_id' => $first->id,
            'building' => 'HQ Building',
            'floor' => 'First Floor',
            'created_by' => $admin->id,
        ]);

        Location::create([
            'name' => 'Main Entrance',
            'type' => LocationType::Checkpoint,
            'parent_id' => $ground->id,
            'building' => 'HQ Building',
            'floor' => 'Ground Floor',
            'notes' => 'Security checkpoint at main entrance',
            'created_by' => $admin->id,
        ]);

        // --- Tasks ---
        Task::create([
            'title' => 'Clean Conference Room A',
            'description' => 'Deep clean the conference room including tables, chairs, and windows.',
            'location' => 'Room-101',
            'category' => TaskCategory::Cleaning,
            'priority' => TaskPriority::High,
            'status' => TaskStatus::Pending,
            'due_date' => now()->addDay(),
            'supervisor_id' => $supervisor->id,
            'assigned_to' => $worker1->id,
        ]);

        Task::create([
            'title' => 'Fix Leaky Faucet',
            'description' => 'Kitchen faucet on 2nd floor is dripping.',
            'location' => 'Room-205',
            'category' => TaskCategory::Maintenance,
            'priority' => TaskPriority::Urgent,
            'status' => TaskStatus::InProgress,
            'due_date' => now(),
            'supervisor_id' => $supervisor->id,
            'assigned_to' => $worker1->id,
        ]);

        Task::create([
            'title' => 'Inspect Fire Extinguishers',
            'description' => 'Check all fire extinguishers on 3rd floor for expiration dates.',
            'location' => 'Floor-3',
            'category' => TaskCategory::Inspection,
            'priority' => TaskPriority::Medium,
            'status' => TaskStatus::Pending,
            'due_date' => now()->addDays(3),
            'supervisor_id' => $supervisor->id,
            'assigned_to' => $worker2->id,
        ]);

        Task::create([
            'title' => 'Vacuum Lobby Area',
            'description' => 'Vacuum and mop the main lobby.',
            'location' => 'Lobby-Main',
            'category' => TaskCategory::Cleaning,
            'priority' => TaskPriority::Low,
            'status' => TaskStatus::Completed,
            'due_date' => now()->subDay(),
            'supervisor_id' => $supervisor->id,
            'assigned_to' => $worker2->id,
        ]);
    }
}
