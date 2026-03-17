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
        Schema::table('rm_sauces', function (Blueprint $table) {
            $table->uuid('material_uuid')->nullable()->after('raw_material_uuid');
            $table->enum('material_type', ['raw', 'premix'])->default('raw')->after('material_uuid');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rm_sauces', function (Blueprint $table) {
            $table->dropColumn(['material_uuid', 'material_type']);
        });
    }
};
