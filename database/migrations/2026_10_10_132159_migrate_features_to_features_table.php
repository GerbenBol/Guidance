<?php

use App\Models\Feat;
use App\Models\PlayerClass;
use App\Models\Race;
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
        foreach (PlayerClass::all() as $class) {
            foreach ($class->features ?? [] as $feature) {
                if ($feature['name'] != 'just some feature') {
                    $class->features()->create($feature);
                }
            }
        }

        foreach (Feat::all() as $feat) {
            foreach ($feat->features ?? [] as $feature) {
                $feat->features()->create($feature);
            }
        }

        foreach (Race::all() as $race) {
            foreach ($race->features ?? [] as $feature) {
                $race->features()->create($feature);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            //
        });
    }
};
