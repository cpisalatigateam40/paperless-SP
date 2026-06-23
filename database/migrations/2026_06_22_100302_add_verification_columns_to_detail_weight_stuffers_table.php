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
        Schema::table('detail_weight_stuffers', function (Blueprint $table) {
            // Weight
            $table->enum('weight_status', ['OK', 'NOT OK'])->nullable()->after('fla_standard');
            $table->text('weight_corrective_action')->nullable()->after('weight_status');
            $table->text('weight_notes')->nullable()->after('weight_corrective_action');

            // Long
            $table->enum('long_status', ['OK', 'NOT OK'])->nullable()->after('weight_notes');
            $table->text('long_corrective_action')->nullable()->after('long_status');
            $table->text('long_notes')->nullable()->after('long_corrective_action');

            // FLA
            $table->enum('fla_status', ['OK', 'NOT OK'])->nullable()->after('long_notes');
            $table->text('fla_corrective_action')->nullable()->after('fla_status');
            $table->text('fla_notes')->nullable()->after('fla_corrective_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_weight_stuffers', function (Blueprint $table) {
            $table->dropColumn([
                'weight_status',
                'weight_corrective_action',
                'weight_notes',

                'long_status',
                'long_corrective_action',
                'long_notes',

                'fla_status',
                'fla_corrective_action',
                'fla_notes',
            ]);
        });
    }
};
