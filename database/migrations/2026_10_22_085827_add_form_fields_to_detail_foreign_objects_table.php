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
        Schema::table('detail_foreign_objects', function (Blueprint $table) {
            // B - Contamination Details
            $table->uuid('contamination_type_uuid')->nullable()->after('contaminant_type');
            $table->string('size_quantity')->nullable()->after('contamination_type_uuid');
            $table->text('description')->nullable()->after('size_quantity');

            // A - Event Information
            $table->string('process_stage')->nullable()->after('gramase');
            $table->string('line')->nullable()->after('process_stage');

            // C - Product Control & Disposition
            $table->string('product_status')->nullable()->after('description');
            $table->string('affected_quantity')->nullable()->after('product_status');
            $table->string('disposition')->nullable()->after('affected_quantity');

            // D - Investigation & Corrective Action
            $table->text('investigation')->nullable()->after('disposition');
            $table->text('immediate_correction')->nullable()->after('investigation');
            $table->string('corrective_action')->nullable()->after('immediate_correction');

            $table->foreign('contamination_type_uuid')
                ->references('uuid')->on('contamination_types')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_foreign_objects', function (Blueprint $table) {
            $table->dropForeign(['contamination_type_uuid']);
            $table->dropColumn([
                'contamination_type_uuid',
                'size_quantity',
                'description',
                'process_stage',
                'line',
                'product_status',
                'affected_quantity',
                'disposition',
                'investigation',
                'immediate_correction',
                'corrective_action',
            ]);
        });
    }
};
