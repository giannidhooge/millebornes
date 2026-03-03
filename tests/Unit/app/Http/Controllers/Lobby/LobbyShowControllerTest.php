<?php

namespace Tests\Unit;

use App\Events\PlayerJoinsLobby;
use App\Models\Game;
use App\Models\Player;
use App\Models\Lobby;
use App\Http\Requests\Lobby\LobbyJoinRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LobbyShowControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $route = 'lobbies.show';

    #[Test]
    public function it_fails_when_code_does_not_exist(): void
    {
        $this->get(route($this->route, ['lobby' => 'XXXX']), [])->assertNotFound();
    }

    #[Test]
    public function it_does_not_show_lobby_if_no_authenticated_player(): void
    {
        $lobby = Lobby::factory()->create(['code' => 'ABCD']);

        $this->get(route($this->route, ['lobby' => 'ABCD']))
            ->assertForbidden();
    }

    #[Test]
    public function it_does_not_show_lobby_if_not_joined(): void
    {
        $lobby = Lobby::factory()->create(['code' => 'WXYZ']);
        Game::factory()->create([
            'unique_identifier' => '4de17e04-8984-43de-a7a8-33c2db7461a5',
            'lobby_id' => $lobby->id,
        ]);

        $otherLobby = Lobby::factory()->create(['code' => 'ABCD']);
        $otherGame = Game::factory()->create([
            'unique_identifier' => '7d5962d8-97b0-4ddd-ae1c-9c818d834477',
            'lobby_id' => $otherLobby->id,
        ]);

        $player = Player::factory()->create([
            'game_id' => $otherGame->id,
            'name' => 'Player',
        ]);

        $this->actingAs($player);

        $this->get(route($this->route, ['lobby' => 'WXYZ']))
            ->assertForbidden();
    }

    #[Test]
    public function it_can_show_lobby(): void
    {
        $lobby = Lobby::factory()->create(['code' => 'ABCD']);
        $game = Game::factory()->create([
            'unique_identifier' => '7d5962d8-97b0-4ddd-ae1c-9c818d834477',
            'lobby_id' => $lobby->id,
        ]);

        $player = Player::factory()->create([
            'game_id' => $game->id,
            'name' => 'Player',
        ]);

        $this->actingAs($player);

        $this->get(route($this->route, ['lobby' => 'ABCD']))
            ->assertInertia(fn(AssertableInertia $page) => $page->component('game/Lobby'));
    }
}