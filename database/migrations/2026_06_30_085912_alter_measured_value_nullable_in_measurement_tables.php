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
        Schema::table('measurement_scales', function (Blueprint $table) {
            $table->decimal('measured_value', 10, 2)->nullable()->change();
        });

        Schema::table('measurement_thermometers', function (Blueprint $table) {
            $table->decimal('measured_value', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('measurement_scales', function (Blueprint $table) {
            $table->decimal('measured_value', 10, 2)->nullable(false)->change();
        });

        Schema::table('measurement_thermometers', function (Blueprint $table) {
            $table->decimal('measured_value', 10, 2)->nullable(false)->change();
        });
    }
};
