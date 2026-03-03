<?php

namespace App\Http\Resources;

use App\Models\Player;
use App\Http\Resources\PlayerResource;
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
            'last_discard_card' => array_last($this->discard_pile),
            'status' => $this->status,
            'current_player' => PlayerResource::make($this->currentPlayer),
            'last_targeted_player' => PlayerResource::make($this->lastTargetedPlayer),
            'players' => $this->players->map(fn(Player $player) => PlayerResource::make($player)),
        ];
    }
}
