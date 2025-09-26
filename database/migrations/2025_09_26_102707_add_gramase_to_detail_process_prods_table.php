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
        Schema::table('detail_process_prods', function (Blueprint $table) {
            $table->decimal('gramase', 8, 2)->nullable()->after('sensory_aroma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_process_prods', function (Blueprint $table) {
            $table->dropColumn('gramase');
        });
    }
};