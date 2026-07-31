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
            $table->string('corrective_action')->nullable()->after('sensory'); // Tindakan Koreksi
            $table->string('keterangan')->nullable()->after('corrective_action');
        });

        Schema::table('detail_sauces', function (Blueprint $table) {
            $table->string('appearance')->nullable()->after('color'); // Kenampakan
            $table->string('product_status')->nullable()->after('texture'); // Release/Reject
            $table->string('corrective_action')->nullable()->after('product_status'); // Tindakan Perbaikan
        });

        Schema::table('report_sauces', function (Blueprint $table) {
            $table->text('documentation_notes')->nullable()->after('gramase'); // Catatan & Dokumentasi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rm_sauces', function (Blueprint $table) {
            $table->dropColumn(['corrective_action', 'keterangan']);
        });
        Schema::table('detail_sauces', function (Blueprint $table) {
            $table->dropColumn(['appearance', 'product_status', 'corrective_action']);
        });
        Schema::table('report_sauces', function (Blueprint $table) {
            $table->dropColumn('documentation_notes');
        });
    }
};
