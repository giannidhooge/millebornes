<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hand' => [],
            'battle_pile' => [],
            'distance_pile' => [],
            'safeties' => [],
            'speed_pile' => [],
            'is_host' => true,
        ];
    }
}
