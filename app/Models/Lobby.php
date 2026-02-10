<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lobby extends Model
{
    protected $guarded = [];

    public function game()
    {
        return $this->hasOne(Game::class);
    }
}