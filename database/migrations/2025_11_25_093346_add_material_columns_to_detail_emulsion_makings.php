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
        Schema::table('detail_emulsion_makings', function (Blueprint $table) {
            // tambahkan kolom baru, nullable agar aman sementara
            $table->uuid('material_uuid')->nullable()->after('raw_material_uuid')->comment('UUID dari RawMaterial atau Premix');
            $table->enum('material_type', ['raw', 'premix'])->default('raw')->after('material_uuid')->comment('sumber bahan: raw = raw material, premix = premix');

            // index sederhana untuk performa lookup
            $table->index(['material_uuid'], 'detail_emulsion_makings_material_uuid_index');
            $table->index(['material_type'], 'detail_emulsion_makings_material_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_emulsion_makings', function (Blueprint $table) {
            // drop index dulu (opsional tapi aman)
            $table->dropIndex('detail_emulsion_makings_material_uuid_index');
            $table->dropIndex('detail_emulsion_makings_material_type_index');

            // drop kolom
            $table->dropColumn(['material_uuid', 'material_type']);
        });
    }
};
