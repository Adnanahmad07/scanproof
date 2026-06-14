<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Location;
use App\Models\User;
use App\Services\LocationImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationCsvImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private LocationImporter $importer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->importer = new LocationImporter();
    }

    public function test_valid_csv_imports_locations(): void
    {
        $csv = "name,type,parent,building,floor,notes\n"
            . "Main Building,building,,,," . "\n"
            . "Ground Floor,floor,Main Building,Main Building,,\n"
            . "Lobby,room,Ground Floor,Main Building,Ground Floor,Main entrance\n";

        $result = $this->importer->import($csv, $this->admin);

        $this->assertEquals(3, $result['created']);
        $this->assertEmpty($result['skipped']);
        $this->assertEmpty($result['errors']);
        $this->assertDatabaseHas('locations', ['name' => 'Main Building', 'type' => 'building']);
        $this->assertDatabaseHas('locations', ['name' => 'Ground Floor', 'type' => 'floor']);
        $this->assertDatabaseHas('locations', ['name' => 'Lobby', 'type' => 'room']);
    }

    public function test_csv_with_skipped_rows_returns_reasons(): void
    {
        Location::create([
            'name' => 'Main Building',
            'type' => 'building',
            'created_by' => $this->admin->id,
        ]);

        $csv = "name,type\n"
            . "Main Building,building\n"
            . "Second Building,building\n";

        $result = $this->importer->import($csv, $this->admin);

        $this->assertEquals(1, $result['created']);
        $this->assertCount(1, $result['skipped']);
        $this->assertStringContainsString('already exists', $result['skipped'][0]['reason']);
    }

    public function test_unknown_type_skips_row(): void
    {
        $csv = "name,type\n"
            . "Test Location,invalidtype\n";

        $result = $this->importer->import($csv, $this->admin);

        $this->assertEquals(0, $result['created']);
        $this->assertCount(1, $result['skipped']);
        $this->assertStringContainsString('Unknown type', $result['skipped'][0]['reason']);
    }

    public function test_missing_parent_skips_row(): void
    {
        $csv = "name,type,parent\n"
            . "Room 1,room,Nonexistent Floor\n";

        $result = $this->importer->import($csv, $this->admin);

        $this->assertEquals(0, $result['created']);
        $this->assertCount(1, $result['skipped']);
        $this->assertStringContainsString('not found', $result['skipped'][0]['reason']);
    }

    public function test_empty_name_skips_row(): void
    {
        $csv = "name,type\n"
            . ",room\n";

        $result = $this->importer->import($csv, $this->admin);

        $this->assertEquals(0, $result['created']);
        $this->assertCount(1, $result['skipped']);
        $this->assertStringContainsString('Name is required', $result['skipped'][0]['reason']);
    }

    public function test_empty_csv_returns_error(): void
    {
        $result = $this->importer->import('', $this->admin);

        $this->assertEquals(0, $result['created']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_missing_required_header_returns_error(): void
    {
        $csv = "name,notes\n"
            . "Test,Some note\n";

        $result = $this->importer->import($csv, $this->admin);

        $this->assertEquals(0, $result['created']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('Missing required header', $result['errors'][0]['message']);
    }

    public function test_duplicate_within_csv_skips(): void
    {
        $csv = "name,type\n"
            . "Room A,room\n"
            . "Room A,room\n";

        $result = $this->importer->import($csv, $this->admin);

        $this->assertEquals(1, $result['created']);
        $this->assertCount(1, $result['skipped']);
        $this->assertStringContainsString('Duplicate', $result['skipped'][0]['reason']);
    }

    public function test_parent_references_resolved_from_csv(): void
    {
        $csv = "name,type,parent\n"
            . "Building B,building,\n"
            . "Floor 1,floor,Building B\n"
            . "Room 101,room,Floor 1\n";

        $result = $this->importer->import($csv, $this->admin);

        $this->assertEquals(3, $result['created']);
        $this->assertEmpty($result['skipped']);

        $room = Location::where('name', 'Room 101')->first();
        $this->assertNotNull($room);
        $this->assertEquals('room', $room->type->value);

        $floor = Location::where('name', 'Floor 1')->first();
        $this->assertEquals($floor->id, $room->parent_id);
    }

    public function test_bom_stripped_from_csv(): void
    {
        $csv = "\xEF\xBB\xBF" . "name,type\n"
            . "BOM Room,room\n";

        $result = $this->importer->import($csv, $this->admin);

        $this->assertEquals(1, $result['created']);
        $this->assertDatabaseHas('locations', ['name' => 'BOM Room']);
    }
}
