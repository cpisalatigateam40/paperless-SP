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
        Schema::table('detail_metal_detectors', function (Blueprint $table) {
            $table->string('verif_loma')
                ->nullable()
                ->after('result_sus316');
            $table->string('nonconformity')
                ->nullable()
                ->after('verif_loma');
            $table->string('corrective_action')
                ->nullable()
                ->after('nonconformity');
            $table->string('verif_after_correct')
                ->nullable()
                ->after('corrective_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_metal_detectors', function (Blueprint $table) {
            $table->dropColumn('verif_loma');
            $table->dropColumn('nonconformity');
            $table->dropColumn('corrective_action');
            $table->dropColumn('verif_after_correct');
        });
    }
};