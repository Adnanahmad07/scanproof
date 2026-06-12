<?php

namespace App\Enums;

enum TaskCategory: string
{
    case Cleaning = 'cleaning';
    case Maintenance = 'maintenance';
    case Inspection = 'inspection';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cleaning => 'Cleaning',
            self::Maintenance => 'Maintenance',
            self::Inspection => 'Inspection',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Cleaning => 'success',
            self::Maintenance => 'info',
            self::Inspection => 'warning',
            self::Other => 'text-secondary',
        };
    }
}
