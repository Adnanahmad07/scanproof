<?php

namespace Tests\Unit;

use App\Enums\LocationType;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_generates_uuid_on_creation(): void
    {
        $location = Location::create([
            'name' => 'Test',
            'type' => LocationType::Room,
        ]);

        $this->assertNotNull($location->uuid);
        $this->assertIsString($location->uuid);
    }

    public function test_location_scan_url_returns_correct_format(): void
    {
        $location = Location::create([
            'name' => 'Test',
            'type' => LocationType::Room,
            'uuid' => 'test-uuid-123',
        ]);

        $this->assertEquals(url('/r/test-uuid-123'), $location->scanUrl());
    }

    public function test_location_type_building_allows_no_parent(): void
    {
        $this->assertTrue(LocationType::Building->allowsParent(null));
    }

    public function test_location_type_floor_allows_building_parent(): void
    {
        $this->assertTrue(LocationType::Floor->allowsParent(LocationType::Building));
        $this->assertFalse(LocationType::Floor->allowsParent(LocationType::Room));
    }

    public function test_location_type_room_allows_building_or_floor_parent(): void
    {
        $this->assertTrue(LocationType::Room->allowsParent(LocationType::Building));
        $this->assertTrue(LocationType::Room->allowsParent(LocationType::Floor));
        $this->assertFalse(LocationType::Room->allowsParent(LocationType::Room));
    }

    public function test_location_type_asset_allows_building_floor_or_room_parent(): void
    {
        $this->assertTrue(LocationType::Asset->allowsParent(LocationType::Building));
        $this->assertTrue(LocationType::Asset->allowsParent(LocationType::Floor));
        $this->assertTrue(LocationType::Asset->allowsParent(LocationType::Room));
        $this->assertFalse(LocationType::Asset->allowsParent(LocationType::Asset));
    }

    public function test_location_for_supervisor_scope_filters_correctly(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $ownLocation = Location::create([
            'name' => 'Own',
            'type' => LocationType::Room,
            'supervisor_id' => $supervisor->id,
        ]);

        $createdLocation = Location::create([
            'name' => 'Created',
            'type' => LocationType::Room,
            'created_by' => $supervisor->id,
        ]);

        $otherLocation = Location::create([
            'name' => 'Other',
            'type' => LocationType::Room,
        ]);

        $results = Location::forSupervisor($supervisor->id)->get();

        $this->assertTrue($results->contains('id', $ownLocation->id));
        $this->assertTrue($results->contains('id', $createdLocation->id));
        $this->assertFalse($results->contains('id', $otherLocation->id));
    }

    public function test_location_parent_relationship(): void
    {
        $building = Location::create([
            'name' => 'Building A',
            'type' => LocationType::Building,
        ]);

        $room = Location::create([
            'name' => 'Room 101',
            'type' => LocationType::Room,
            'parent_id' => $building->id,
        ]);

        $this->assertInstanceOf(Location::class, $room->parent);
        $this->assertEquals($building->id, $room->parent->id);
    }

    public function test_location_children_relationship(): void
    {
        $building = Location::create([
            'name' => 'Building A',
            'type' => LocationType::Building,
        ]);

        Location::create([
            'name' => 'Room 101',
            'type' => LocationType::Room,
            'parent_id' => $building->id,
        ]);

        Location::create([
            'name' => 'Room 102',
            'type' => LocationType::Room,
            'parent_id' => $building->id,
        ]);

        $this->assertCount(2, $building->children);
    }
}
