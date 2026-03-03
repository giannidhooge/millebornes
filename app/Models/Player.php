<?php

namespace App\Models;

use App\Traits\UniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Player extends Authenticatable
{
    use HasFactory;
    use UniqueIdentifier;

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

    public function hasSafety(string $safetySubtype): bool
    {
        return collect($this->safeties, function ($safety) use ($safetySubtype) {
            return $safety['subtype'] === $safetySubtype;
        })->count() > 0;
    }

    public function getTopBattleCard(): ?array
    {
        return array_last($this->battle_pile);
    }

    public function getTopSpeedCard(): ?array
    {
        return array_last($this->speed_pile);
    }

    public function hasActiveSpeedLimit(): bool
    {
        $top = $this->getTopSpeedCard();
        return $top !== null && $top['subtype'] === 'speed_limit';
    }

    public function getTotalMileage(): int
    {
        return array_reduce($this->distance_pile, function ($carry, $card) {
            return $carry + (int)($card['value'] ?? 0);
        }, 0);
    }

    public function getDistanceCardCount(string $value): int
    {
        return count(array_filter($this->distance_pile, function (array $card) use ($value) {
            return (string)$card['value'] === $value;
        }));
    }

    public function addCardToPile(array $card, string $pileName): void
    {
        $pile = $this->{$pileName};
        $pile[] = $card;
        $this->{$pileName} = $pile;
    }
}