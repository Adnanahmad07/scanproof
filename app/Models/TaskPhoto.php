<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'type',
        'path',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
