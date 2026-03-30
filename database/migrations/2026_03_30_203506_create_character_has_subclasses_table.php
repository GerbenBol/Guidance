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
        Schema::create('character_has_subclasses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('character_id')->unsigned();
            $table->bigInteger('class_id')->unsigned();
            $table->json('class_options')->nullable();

            $table->foreign('character_id')->references('id')->on('characters')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('subclasses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_has_subclasses');
    }
};
