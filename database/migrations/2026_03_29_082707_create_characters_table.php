<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->bigInteger('race_id')->nullable()->unsigned();
            $table->bigInteger('background_id')->nullable()->unsigned();
            $table->json('race_options')->nullable();
            $table->json('background_options')->nullable();
            $table->bigInteger('player_id')->unsigned();

            $table->foreign('player_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('race_id')->references('id')->on('races')->onDelete('set null');
            // $table->foreign('background_id')->references('id')->on('backgrounds')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
