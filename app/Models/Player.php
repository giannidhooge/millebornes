<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Player extends Authenticatable
{
    protected $guarded = [];

    protected $casts = [
        'hand' => 'array',
        'safeties' => 'array',
        'battle_pile' => 'array',
        'distance_pile' => 'array',
        'speed_pile' => 'array',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}