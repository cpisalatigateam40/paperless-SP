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
        Schema::table('formulations', function (Blueprint $table) {
            $table->uuid('premix_uuid')->nullable()->after('raw_material_uuid');
            $table->foreign('premix_uuid')->references('uuid')->on('premixes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formulations', function (Blueprint $table) {
            $table->dropForeign(['premix_uuid']);
            $table->dropColumn('premix_uuid');
        });
    }
};