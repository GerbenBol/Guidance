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
        Schema::create('feats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('system_version')->nullable();
            $table->longText('description')->nullable();
            $table->longText('short_desc')->nullable();
            $table->json('features')->nullable();
            $table->json('requirements')->nullable();
            $table->bigInteger('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feats');
    }
};
