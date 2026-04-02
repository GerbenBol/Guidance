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
        Schema::create('spells', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->longText('short_desc')->nullable();
            $table->longText('description')->nullable();
            $table->integer('level')->unsigned();
            $table->json('components')->nullable();
            $table->string('arearange')->nullable();
            $table->json('casting_time')->nullable();
            $table->json('duration')->nullable();
            $table->json('effect')->nullable();
            $table->json('scaling')->nullable();
            $table->bigInteger('school_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spells');
    }
};
