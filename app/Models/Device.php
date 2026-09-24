<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = ['name', 'timeout_seconds'];

    protected function casts(): array
    {
        return ['last_seen_at' => 'immutable_datetime', 'last_checked_at' => 'immutable_datetime'];
    }
}
