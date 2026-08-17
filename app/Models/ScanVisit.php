<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanVisit extends Model
{
    protected $fillable = [
        'location_id',
        'uuid',
        'ip_address',
        'user_agent',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
