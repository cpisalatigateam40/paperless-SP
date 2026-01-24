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
        Schema::table('detail_rm_arrivals', function (Blueprint $table) {
            $table->uuid('material_uuid')
        ->nullable()
        ->after('raw_material_uuid')
        ->comment('UUID RawMaterial atau Premix');

        $table->enum('material_type', ['raw', 'premix'])
            ->default('raw')
            ->after('material_uuid');

        $table->index('material_uuid');
        $table->index('material_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_rm_arrivals', function (Blueprint $table) {
            $table->dropIndex(['material_uuid']);
            $table->dropIndex(['material_type']);
            $table->dropColumn(['material_uuid', 'material_type']);
        });
    }
};
