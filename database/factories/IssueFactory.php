<?php

namespace Database\Factories;

use App\Enums\IssueStatus;
use App\Models\Issue;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IssueFactory extends Factory
{
    protected $model = Issue::class;

    public function definition(): array
    {
        return [
            'tracking_code' => Issue::generateTrackingCode(),
            'location_id' => Location::factory(),
            'description' => $this->faker->sentence(10),
            'status' => IssueStatus::Reported,
            'reported_by' => User::factory(),
        ];
    }

    public function reported(): static
    {
        return $this->state(['status' => IssueStatus::Reported]);
    }

    public function assigned(): static
    {
        return $this->state([
            'status' => IssueStatus::Assigned,
            'assigned_to' => User::factory(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state([
            'status' => IssueStatus::InProgress,
            'assigned_to' => User::factory(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state([
            'status' => IssueStatus::Resolved,
            'assigned_to' => User::factory(),
            'resolved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => IssueStatus::Rejected,
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }
}
