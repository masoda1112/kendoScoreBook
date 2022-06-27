<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_tech', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->boolean('valid');
            $table->boolean('competitor');
            $table->foreignId('game_id');
            $table->foreignId('tech_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tech_games');
    }
};
