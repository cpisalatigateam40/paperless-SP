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
        Schema::table('checklist_packaging_details', function (Blueprint $table) {
            $table->integer('sampling_amount')->nullable()->after('notes');
            $table->string('unit')->nullable()->after('sampling_amount');
            $table->string('sampling_result')->nullable()->after('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklist_packaging_details', function (Blueprint $table) {
             $table->dropColumn(['sampling_amount', 'unit', 'sampling_result']);
        });
    }
};