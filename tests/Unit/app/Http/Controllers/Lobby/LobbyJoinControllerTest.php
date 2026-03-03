<?php

namespace Tests\Unit;

use App\Events\PlayerJoinsLobby;
use App\Models\Game;
use App\Models\Lobby;
use App\Http\Requests\Lobby\LobbyJoinRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LobbyJoinControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $route = 'lobbies.join';

    #[Test]
    public function it_fails_when_name_is_missing(): void
    {
        $lobby = Lobby::factory()->create();

        $this->post(route($this->route), [
            'code' => $lobby->code,
        ])->assertRedirect()
          ->assertSessionHasErrors([
            LobbyJoinRequest::NAME => 'The name field is required.',
        ]);
    }

    #[Test]
    public function it_fails_when_code_is_missing(): void
    {
        $this->post(route($this->route), [
            'name' => 'Player 2',
        ])->assertRedirect()
          ->assertSessionHasErrors([
            LobbyJoinRequest::CODE => 'The code field is required.',
        ]);
    }

    #[Test]
    public function it_fails_when_code_does_not_exist(): void
    {
        $this->post(route($this->route), [
            'name' => 'Player 2',
            'code' => 'XXXX',
         ])->assertRedirect()
          ->assertSessionHasErrors([
            LobbyJoinRequest::CODE => 'The selected code is invalid.',
        ]);
    }

    #[Test]
    public function it_can_join_lobby_with_valid_name_and_code(): void
    {
        Event::fake();

        $lobby = Lobby::factory()->create();
        Game::factory()->create([
            'unique_identifier' => '7d5962d8-97b0-4ddd-ae1c-9c818d834477',
            'lobby_id' => $lobby->id,
        ]);

        $response = $this->post(route($this->route), [
            'name' => 'Player 2',
            'code' => $lobby->code,
        ]);

        $response->assertRedirect(route('lobbies.show', $lobby->code));

        $players = $lobby->game->players()->get();

        $this->assertCount(1, $players);
        $this->assertEquals('Player 2', $players->first()->name);
        $this->assertFalse((bool) $players->first()->is_host);

        Event::assertDispatched(PlayerJoinsLobby::class, function ($event) use ($lobby, $players) {
            return $event->lobby->is($lobby) && $event->player->is($players->first());
        });
    }
}