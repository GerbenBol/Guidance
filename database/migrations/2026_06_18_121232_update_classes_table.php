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
        Schema::table('classes', function (Blueprint $table) {
            $table->json('class_info')->nullable()->after('description');
            $table->string('hit_die')->nullable()->after('spell_info');
            $table->integer('max_levels')->default(20)->after('hit_die');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('class_info');
            $table->dropColumn('hit_die');
            $table->dropColumn('max_levels');
        });
    }
};
