<?php

namespace App\Services;

use App\Enums\LocationType;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocationImporter
{
    private const REQUIRED_HEADERS = ['name', 'type'];

    private const OPTIONAL_HEADERS = ['parent', 'building', 'floor', 'notes'];

    private const MAX_ROWS = 5000;

    /**
     * Import locations from CSV contents.
     *
     * @return array{created: int, skipped: list<array{row: int, reason: string}>, errors: list<array{row: int, message: string}>}
     */
    public function import(string $csvContents, User $admin): array
    {
        $rows = $this->parseCsv($csvContents);

        if ($rows === []) {
            return ['created' => 0, 'skipped' => [], 'errors' => [['row' => 0, 'message' => 'CSV file is empty or has no data rows.']]];
        }

        $headers = array_map('strtolower', array_keys($rows[0]));
        $validation = $this->validateHeaders($headers);

        if ($validation !== null) {
            return ['created' => 0, 'skipped' => [], 'errors' => [['row' => 0, 'message' => $validation]]];
        }

        if (count($rows) > self::MAX_ROWS) {
            return ['created' => 0, 'skipped' => [], 'errors' => [['row' => 0, 'message' => 'CSV exceeds maximum of ' . self::MAX_ROWS . ' rows.']]];
        }

        $created = 0;
        $skipped = [];
        $errors = [];
        $pendingByName = [];

        $result = DB::transaction(function () use ($rows, $headers, $admin, &$created, &$skipped, &$errors, &$pendingByName) {
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // +1 for 1-based, +1 for header row

                $data = array_change_key_case($row, CASE_LOWER);
                $name = trim($data['name'] ?? '');
                $typeStr = strtolower(trim($data['type'] ?? ''));

                if ($name === '') {
                    $skipped[] = ['row' => $rowNum, 'reason' => 'Name is required.'];
                    continue;
                }

                if (!in_array($typeStr, LocationType::values(), true)) {
                    $skipped[] = ['row' => $rowNum, 'reason' => "Unknown type '{$typeStr}'."];
                    continue;
                }

                $type = LocationType::from($typeStr);
                $parentName = trim($data['parent'] ?? '');
                $building = trim($data['building'] ?? '') ?: null;
                $floor = trim($data['floor'] ?? '') ?: null;
                $notes = trim($data['notes'] ?? '') ?: null;

                $parentId = null;
                if ($parentName !== '') {
                    $parentKey = strtolower($parentName);
                    if (isset($pendingByName[$parentKey])) {
                        $parentId = $pendingByName[$parentKey];
                    } else {
                        $existingParent = Location::whereRaw('lower(name) = lower(?)', [$parentName])->first();
                        if (!$existingParent) {
                            $skipped[] = ['row' => $rowNum, 'reason' => "Parent '{$parentName}' not found."];
                            continue;
                        }
                        $parentId = $existingParent->id;
                    }
                }

                $lookupKey = strtolower($name) . '|' . ($parentId ?? '') . '|' . $type->value;
                if (isset($pendingByName[$lookupKey])) {
                    $skipped[] = ['row' => $rowNum, 'reason' => "Duplicate '{$name}' within CSV."];
                    continue;
                }

                $existsInDb = Location::whereRaw('lower(name) = lower(?)', [$name])
                    ->where('type', $type)
                    ->where('parent_id', $parentId)
                    ->exists();

                if ($existsInDb) {
                    $skipped[] = ['row' => $rowNum, 'reason' => "Location '{$name}' already exists."];
                    continue;
                }

                $location = Location::create([
                    'name' => $name,
                    'type' => $type,
                    'parent_id' => $parentId,
                    'building' => $building,
                    'floor' => $floor,
                    'notes' => $notes,
                    'created_by' => $admin->id,
                ]);

                $pendingByName[$lookupKey] = $location->id;
                $created++;
            }

            return compact('created', 'skipped', 'errors');
        });

        return $result;
    }

    /**
     * Parse CSV contents into an array of associative rows.
     */
    private function parseCsv(string $contents): array
    {
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);
        $contents = str_replace("\r\n", "\n", $contents);

        $lines = explode("\n", $contents);
        $lines = array_filter($lines, fn ($line) => trim($line) !== '');

        if (count($lines) < 2) {
            return [];
        }

        $headers = $this->parseCsvLine(array_shift($lines));
        $rows = [];

        foreach ($lines as $line) {
            $values = $this->parseCsvLine($line);
            if (count($values) === count($headers)) {
                $rows[] = array_combine($headers, $values);
            }
        }

        return $rows;
    }

    private function parseCsvLine(string $line): array
    {
        $row = [];
        $fp = fopen('php://temp', 'r+');
        fwrite($fp, $line);
        rewind($fp);
        $row = fgetcsv($fp);
        fclose($fp);

        return $row ?: [];
    }

    private function validateHeaders(array $headers): ?string
    {
        foreach (self::REQUIRED_HEADERS as $required) {
            if (!in_array($required, $headers, true)) {
                return "Missing required header: '{$required}'.";
            }
        }

        return null;
    }
}
