<?php

namespace Database\Factories;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->unique()->streetName(),
            'type' => LocationType::Room,
            'parent_id' => null,
            'building' => null,
            'floor' => null,
            'notes' => null,
            'created_by' => null,
        ];
    }

    public function ofType(LocationType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    public function childOf(Location $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'building' => $parent->building ?? $parent->name,
        ]);
    }
}
