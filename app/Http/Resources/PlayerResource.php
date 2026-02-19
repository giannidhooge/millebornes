<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'unique_identifier' => $this->unique_identifier,
            'safeties' => $this->safeties,
            'distance' => $this->getTotalMileage(),
            'battle_pile' => $this->battle_pile,
            'speed_pile' => $this->speed_pile,
            'last_battle_pile_card' => array_last($this->battle_pile),
            'last_speed_pile_card' => array_last($this->speed_pile),
        ];
    }
}
