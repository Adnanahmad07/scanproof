<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WorkerImporter
{
    private const REQUIRED_HEADERS = ['name', 'email'];

    private const OPTIONAL_HEADERS = ['password'];

    private const MAX_ROWS = 5000;

    private const DEFAULT_PASSWORD = 'password';

    /**
     * Import workers from CSV contents.
     *
     * @return array{created: int, skipped: list<array{row: int, reason: string}>, errors: list<array{row: int, message: string}>}
     */
    public function import(string $csvContents, ?int $supervisorId = null): array
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

        $result = DB::transaction(function () use ($rows, $supervisorId, &$created, &$skipped, &$errors) {
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2;

                $data = array_change_key_case($row, CASE_LOWER);
                $name = trim($data['name'] ?? '');
                $email = strtolower(trim($data['email'] ?? ''));

                if ($name === '') {
                    $skipped[] = ['row' => $rowNum, 'reason' => 'Name is required.'];
                    continue;
                }

                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skipped[] = ['row' => $rowNum, 'reason' => 'Valid email is required.'];
                    continue;
                }

                if (User::where('email', $email)->exists()) {
                    $skipped[] = ['row' => $rowNum, 'reason' => "A user with email '{$email}' already exists."];
                    continue;
                }

                $password = trim($data['password'] ?? '') ?: self::DEFAULT_PASSWORD;

                User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => UserRole::Staff,
                    'supervisor_id' => $supervisorId,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                $created++;
            }

            return compact('created', 'skipped', 'errors');
        });

        return $result;
    }

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
