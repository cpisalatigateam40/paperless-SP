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
        Schema::table('checklist_packaging_details', function (Blueprint $table) {
            $table->decimal('standard_long_pcs', 8, 2)->nullable();
            $table->decimal('actual_long_pcs_1', 8, 2)->nullable();
            $table->decimal('actual_long_pcs_2', 8, 2)->nullable();
            $table->decimal('actual_long_pcs_3', 8, 2)->nullable();
            $table->decimal('actual_long_pcs_4', 8, 2)->nullable();
            $table->decimal('actual_long_pcs_5', 8, 2)->nullable();
            $table->decimal('avg_long_pcs', 8, 2)->nullable();

            $table->decimal('standard_weight_pcs', 8, 2)->nullable();
            $table->decimal('actual_weight_pcs_1', 8, 2)->nullable();
            $table->decimal('actual_weight_pcs_2', 8, 2)->nullable();
            $table->decimal('actual_weight_pcs_3', 8, 2)->nullable();
            $table->decimal('actual_weight_pcs_4', 8, 2)->nullable();
            $table->decimal('actual_weight_pcs_5', 8, 2)->nullable();
            $table->decimal('avg_weight_pcs', 8, 2)->nullable();

            $table->decimal('avg_weight', 8, 2)->nullable()->after('actual_weight_5');

            $table->string('verif_md')->nullable();
            $table->string('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklist_packaging_details', function (Blueprint $table) {
            $table->dropColumn('standard_long_pcs');
            $table->dropColumn('actual_long_pcs_1');
            $table->dropColumn('actual_long_pcs_2');
            $table->dropColumn('actual_long_pcs_3');
            $table->dropColumn('actual_long_pcs_4');
            $table->dropColumn('actual_long_pcs_5');
            $table->dropColumn('avg_long_pcs');
            
            $table->dropColumn('standard_weight_pcs');
            $table->dropColumn('actual_weight_pcs_1');
            $table->dropColumn('actual_weight_pcs_2');
            $table->dropColumn('actual_weight_pcs_3');
            $table->dropColumn('actual_weight_pcs_4');
            $table->dropColumn('actual_weight_pcs_5');
            $table->dropColumn('avg_weight_pcs');
            $table->dropColumn('avg_weight');
            $table->dropColumn('verif_md');
            $table->dropColumn('notes');
        });
    }
};