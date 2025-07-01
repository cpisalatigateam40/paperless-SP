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
        Schema::table('detail_magnet_traps', function (Blueprint $table) {
            $table->dropColumn('finding');
            $table->string('finding_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_magnet_traps', function (Blueprint $table) {
            $table->dropColumn('finding_image');
            $table->text('finding')->nullable();
        });
    }
};