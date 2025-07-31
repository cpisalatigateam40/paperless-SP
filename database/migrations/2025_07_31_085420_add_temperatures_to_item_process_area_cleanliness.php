<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('item_process_area_cleanliness', function (Blueprint $table) {
            $table->decimal('temperature_actual', 5, 1)->nullable();
            $table->decimal('temperature_display', 5, 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_process_area_cleanliness', function (Blueprint $table) {
            $table->dropColumn('temperature_actual');
            $table->dropColumn('temperature_display');
        });
    }

};