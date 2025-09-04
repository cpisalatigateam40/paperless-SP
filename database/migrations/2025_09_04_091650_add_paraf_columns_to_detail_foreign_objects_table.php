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
        Schema::table('detail_foreign_objects', function (Blueprint $table) {
            $table->text('qc_paraf')->nullable();
            $table->text('production_paraf')->nullable();
            $table->text('engineering_paraf')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_foreign_objects', function (Blueprint $table) {
            $table->dropColumn(['qc_paraf', 'production_paraf', 'engineering_paraf']);
        });
    }
};