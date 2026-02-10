<?php

namespace App\Events;

use App\Models\Player;
use App\Models\Lobby;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerJoinsLobby implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(Lobby $lobby, Player $player)
    {
        $this->lobby = $lobby;
        $this->player = $player;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('lobby.' . $this->lobby->code),
        ];
    }

    public function broadcastWith()
    {
        return [
            'player' => $this->player->toArray(),
        ];
    }
}
