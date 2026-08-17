<?php

namespace App\Enums;

enum LocationType: string
{
    // Legacy types (backward compatible)
    case Building = 'building';
    case Floor = 'floor';
    case Room = 'room';
    case Asset = 'asset';
    case Checkpoint = 'checkpoint';

    // New simple types for QR setup
    case Lobby = 'lobby';
    case Office = 'office';
    case Kitchen = 'kitchen';
    case Bathroom = 'bathroom';
    case Corridor = 'corridor';
    case Storage = 'storage';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Building => 'Building',
            self::Floor => 'Floor',
            self::Room => 'Room',
            self::Asset => 'Asset',
            self::Checkpoint => 'Checkpoint',
            self::Lobby => 'Lobby',
            self::Office => 'Office',
            self::Kitchen => 'Kitchen',
            self::Bathroom => 'Bathroom',
            self::Corridor => 'Corridor',
            self::Storage => 'Storage',
            self::Other => 'Other',
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
            self::Lobby => 'primary',
            self::Office => 'info',
            self::Kitchen => 'warning',
            self::Bathroom => 'error',
            self::Corridor => 'info',
            self::Storage => 'warning',
            self::Other => 'info',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Building => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-2 0h-3m-9 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            self::Floor => 'M4 6h16M4 10h16M4 14h16M4 18h16',
            self::Room => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            self::Asset => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            self::Checkpoint => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            self::Lobby => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            self::Office => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-2 0h-3m-9 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            self::Kitchen => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
            self::Bathroom => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            self::Corridor => 'M4 6h16M4 10h16M4 14h16M4 18h16',
            self::Storage => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            self::Other => 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
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
     * New simple types used for QR setup (room configuration).
     * @return list<self>
     */
    public static function simpleTypes(): array
    {
        return [
            self::Lobby,
            self::Office,
            self::Kitchen,
            self::Bathroom,
            self::Corridor,
            self::Storage,
            self::Other,
        ];
    }

    /**
     * Location types that may legally be the parent of this type.
     */
    public function allowedParents(): array
    {
        return match ($this) {
            self::Building => [],
            self::Floor => [self::Building],
            self::Room => [self::Building, self::Floor],
            self::Asset, self::Checkpoint => [self::Building, self::Floor, self::Room],
            // New simple types have no parent hierarchy
            self::Lobby, self::Office, self::Kitchen, self::Bathroom,
            self::Corridor, self::Storage, self::Other => [],
        };
    }

    /**
     * Validate that the given parent type is allowed for this type.
     */
    public function allowsParent(?self $parentType): bool
    {
        if ($parentType === null) {
            return true;
        }

        return in_array($parentType, $this->allowedParents(), true);
    }
}
