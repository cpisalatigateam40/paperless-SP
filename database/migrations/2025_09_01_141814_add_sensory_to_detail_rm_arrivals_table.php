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
        Schema::table('detail_rm_arrivals', function (Blueprint $table) {
            $table->string('sensory_appearance')
                ->nullable()
                ->after('contamination');
            $table->string('sensory_aroma')
                ->nullable()
                ->after('sensory_appearance');
            $table->string('sensory_color')
                ->nullable()
                ->after('sensory_aroma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_rm_arrivals', function (Blueprint $table) {
            $table->dropColumn('sensory_appearance');
            $table->dropColumn('sensory_aroma');
            $table->dropColumn('sensory_color');
        });
    }
};