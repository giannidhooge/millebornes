<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lobby>
 */
class LobbyFactory extends Factory
{
   public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(4)),
        ];
    }
}
