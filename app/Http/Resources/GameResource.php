<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'unique_identifier' => $this->unique_identifier,
            'name' => $this->name,
            'code' => $this->code,
            'deck_size' => count($this->deck),
            'discard_pile' => $this->discard_pile,
            'current_player' => $this->currentPlayer?->toArray(),
            'players' => $this->players->toArray($request),
        ];
    }
}
