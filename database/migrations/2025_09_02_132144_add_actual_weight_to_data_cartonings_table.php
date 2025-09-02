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
        Schema::table('data_cartonings', function (Blueprint $table) {
            $table->float('weight_1')
                ->nullable();
            $table->float('weight_2')
                ->nullable();
            $table->float('weight_3')
                ->nullable();
            $table->float('weight_4')
                ->nullable();
            $table->float('weight_5')
                ->nullable();
            $table->float('avg_weight')
                ->nullable();
            $table->integer('content_rtg')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_cartonings', function (Blueprint $table) {
            $table->dropColumn('weight_1');
            $table->dropColumn('weight_2');
            $table->dropColumn('weight_3');
            $table->dropColumn('weight_4');
            $table->dropColumn('weight_5');
            $table->dropColumn('avg_weight');
            $table->dropColumn('content_rtg');
        });
    }
};