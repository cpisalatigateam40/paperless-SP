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
        Schema::table('detail_process_prods', function (Blueprint $table) {
            $table->string('sensory_homogenity')
                ->nullable();
            $table->string('sensory_stiffness')
                ->nullable();
            $table->string('sensory_aroma')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_process_prods', function (Blueprint $table) {
            $table->dropColumn('sensory_homogenity');
            $table->dropColumn('sensory_stiffness');
            $table->dropColumn('sensory_aroma');
        });
    }
};