<?php

namespace App\Services;

class ImportResult
{
    public function __construct(
        public readonly int $created,
        public readonly array $skipped,
        public readonly array $errors,
    ) {}

    public function hasErrors(): bool
    {
        return $this->errors !== [] || $this->skipped !== [];
    }

    public function summary(): string
    {
        $parts = ["{$this->created} created"];
        if ($this->skipped !== []) {
            $parts[] = count($this->skipped) . ' skipped';
        }
        if ($this->errors !== []) {
            $parts[] = count($this->errors) . ' errors';
        }

        return implode(', ', $parts);
    }
}
