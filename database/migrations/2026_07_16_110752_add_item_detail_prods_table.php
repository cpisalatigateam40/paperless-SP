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
        Schema::table('item_detail_prods', function (Blueprint $table) {
            $table->string('material_name')->nullable()->after('formulation_uuid');
            $table->enum('material_type', ['raw_material', 'premix'])->nullable()->after('material_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_detail_prods', function (Blueprint $table) {
            $table->dropColumn(['material_name', 'material_type']);
        });
    }
};
