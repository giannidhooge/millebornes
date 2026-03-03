<?php

namespace Tests\Unit;

use App\Models\Lobby;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LobbyCreateControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $route = 'lobbies.store';

    #[Test]
    public function it_fails_when_name_is_missing(): void
    {
        $response = $this->postJson(route($this->route), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_fails_when_name_is_invalid(): void
    {
        $response = $this->postJson(route($this->route), [
            'name' => str_repeat('a', 256), // Exceeds typical max length
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_can_create_lobby_with_valid_name(): void
    {
        $response = $this->postJson(route($this->route), [
            'name' => 'Player 1',
        ])->assertRedirect();
        
        // Extract the lobby code from the redirect URL (last segment)
        $redirectUrl = $response->headers->get('Location');
        $code = basename($redirectUrl);

        $lobby = Lobby::where('code', $code)->sole();
        $players = $lobby->game->players()->get();

        $this->assertCount(1, $players);
        $this->assertEquals('Player 1', $players->first()->name);
        $this->assertTrue((bool) $players->first()->is_host);
    }
}