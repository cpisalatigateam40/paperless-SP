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
        // tambah ke detail_process_prods
        Schema::table('detail_process_prods', function (Blueprint $table) {
            $table->string('hasil_penggilingan')->nullable()->after('gramase');
            $table->string('hasil_pencampuran')->nullable()->after('hasil_penggilingan');
        });

        // tambah ke item_detail_prods
        Schema::table('item_detail_prods', function (Blueprint $table) {
            $table->string('keterangan')->nullable()->after('temperature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_process_prods', function (Blueprint $table) {
            $table->dropColumn(['hasil_penggilingan', 'hasil_pencampuran']);
        });
        Schema::table('item_detail_prods', function (Blueprint $table) {
            $table->dropColumn(['keterangan']);
        });
    }
};
