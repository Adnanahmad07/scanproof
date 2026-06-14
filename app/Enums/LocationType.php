<?php

namespace App\Enums;

enum LocationType: string
{
    case Building = 'building';
    case Floor = 'floor';
    case Room = 'room';
    case Asset = 'asset';
    case Checkpoint = 'checkpoint';

    public function label(): string
    {
        return match ($this) {
            self::Building => 'Building',
            self::Floor => 'Floor',
            self::Room => 'Room',
            self::Asset => 'Asset',
            self::Checkpoint => 'Checkpoint',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Building => 'primary',
            self::Floor => 'info',
            self::Room => 'success',
            self::Asset => 'warning',
            self::Checkpoint => 'error',
        };
    }

    /**
     * Heroicon-style SVG path for the type (used in list/cards).
     */
    public function icon(): string
    {
        return match ($this) {
            self::Building => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-2 0h-3m-9 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            self::Floor => 'M4 6h16M4 10h16M4 14h16M4 18h16',
            self::Room => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            self::Asset => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            self::Checkpoint => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    /**
     * Location types that may legally be the parent of this type.
     * Building is always a root (no parent). Higher levels may also be roots.
     *
     * @return list<self>
     */
    public function allowedParents(): array
    {
        return match ($this) {
            self::Building => [],
            self::Floor => [self::Building],
            self::Room => [self::Building, self::Floor],
            self::Asset, self::Checkpoint => [self::Building, self::Floor, self::Room],
        };
    }

    /**
     * Validate that the given parent type is allowed for this type.
     * A null parent (root) is always permitted; admins may create a
     * standalone location of any type. A non-null parent must be a
     * legal type for this child.
     */
    public function allowsParent(?self $parentType): bool
    {
        if ($parentType === null) {
            return true;
        }

        return in_array($parentType, $this->allowedParents(), true);
    }
}
