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
        Schema::table('characters', function (Blueprint $table) {
            $table->json('classes')->nullable()->after('system_version');
            $table->json('inventory')->nullable()->after('background_options');
            $table->json('settings')->nullable()->after('inventory');
            $table->boolean('updated')->default(true)->after('settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('classes');
            $table->dropColumn('inventory');
            $table->dropColumn('settings');
            $table->dropColumn('updated');
        });
    }
};
