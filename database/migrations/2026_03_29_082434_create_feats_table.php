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
            $table->string('name')->unique();
            $table->longText('short_desc')->nullable();
            $table->longText('description')->nullable();
            $table->bigInteger('feature_id')->nullable()->unsigned();
            $table->foreignId('feature_id')->references('id')->on('features')->onDelete('set null');
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
