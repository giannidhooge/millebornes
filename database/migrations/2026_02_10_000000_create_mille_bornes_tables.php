<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lobbies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('unique_identifier', 36)->unique()->index();
            $table->foreignId('lobby_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('waiting'); // waiting, active, finished
            $table->json('deck');
            $table->json('discard_pile');
            $table->unsignedBigInteger('current_player_id')->nullable();
            $table->unsignedBigInteger('last_targeted_player_id')->nullable();
            $table->timestamps();
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('unique_identifier', 36)->unique()->index();
            $table->boolean('is_host')->default(false);
            $table->json('hand');
            $table->json('safeties');
            $table->json('battle_pile');
            $table->json('distance_pile');
            $table->json('speed_pile');
            $table->timestamps();
        });

        Schema::table('games', function (Blueprint $table) {
            $table->foreign('current_player_id')->references('id')->on('players')->nullOnDelete();
            $table->foreign('last_targeted_player_id')->references('id')->on('players')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['current_player_id', 'last_targeted_player_id']);
        });
        Schema::dropIfExists('players');
        Schema::dropIfExists('games');
        Schema::dropIfExists('lobbies');
    }
};