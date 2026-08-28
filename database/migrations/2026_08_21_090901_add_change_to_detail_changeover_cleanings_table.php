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
        Schema::table('detail_changeover_cleanings', function (Blueprint $table) {
            $table->string('group', 30)->default('mesin_peralatan')->after('report_uuid');
            // 'sisa_bahan' | 'mesin_peralatan' | 'kondisi_ruangan'

            $table->string('item_name')->nullable()->after('item_uuid');
            // dipakai untuk item manual (sisa_bahan & kondisi_ruangan), free text

            $table->unsignedTinyInteger('score')->nullable()->after('result');
            // 1-8 sesuai kriteria penilaian di form

            // item_uuid wajib nullable karena item manual tidak referensi master
            $table->uuid('item_uuid')->nullable()->change();
        });

        // Drop FK lama yang RESTRICT, buat ulang jadi nullOnDelete
        // supaya kalau item master dihapus, detail lama tidak ikut ke-block
        Schema::table('detail_changeover_cleanings', function (Blueprint $table) {
            $table->dropForeign(['item_uuid']);
        });

        Schema::table('detail_changeover_cleanings', function (Blueprint $table) {
            $table->foreign('item_uuid')
                ->references('uuid')->on('master_checklist_items')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_changeover_cleanings', function (Blueprint $table) {
            $table->dropForeign(['item_uuid']);
            $table->dropColumn(['group', 'item_name', 'score']);
        });

        Schema::table('detail_changeover_cleanings', function (Blueprint $table) {
            $table->foreign('item_uuid')
                ->references('uuid')->on('master_checklist_items')
                ->restrictOnDelete();
        });
    }
};
