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
        Schema::table('report_process_prods', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('shift');
        });

        Schema::table('detail_process_prods', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('sensory_aroma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_process_prods', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('detail_process_prods', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
