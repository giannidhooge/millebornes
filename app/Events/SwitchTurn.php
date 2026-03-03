<?php

namespace App\Events;
use App\Models\Game;
use App\Http\Resources\GameResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SwitchTurn implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
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
        ];
    }
}
