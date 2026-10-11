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
        Schema::table('feats', function (Blueprint $table) {
            $table->dropColumn('features');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('features');
        });

        Schema::table('races', function (Blueprint $table) {
            $table->dropColumn('features');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feats', function (Blueprint $table) {
            $table->json('features')->nullable();
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->json('features')->nullable();
        });

        Schema::table('races', function (Blueprint $table) {
            $table->json('features')->nullable();
        });
    }
};
