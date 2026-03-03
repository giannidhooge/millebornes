<?php

namespace App\Models;

use App\Traits\UniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;
    use UniqueIdentifier;

    protected $guarded = [];

    protected $casts = [
        'deck' => 'array',
        'discard_pile' => 'array',
    ];

    protected $hidden = [
        'deck',
    ];

    public function lobby(): BelongsTo
    {
        return $this->belongsTo(Lobby::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function currentPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'current_player_id');
    }

    public function lastTargetPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'last_targeted_player_id');
    }

    public function getRouteKeyName(): string
    {
        return 'unique_identifier';
    }
}