<?php

namespace App\Events;
use App\Models\Game;
use App\Http\Resources\GameResource;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerWon implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Game $game;

    private Player $player;

    public function __construct(Game $game, Player $player)
    {
        $this->game = $game;
        $this->player = $player;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('game.' . $this->game->unique_identifier),
        ];
    }

    public function broadcastWith()
    {
        return [
            'game' => GameResource::make($this->game),
            'player' => PlayerResource::make($this->player),
        ];
    }
}
