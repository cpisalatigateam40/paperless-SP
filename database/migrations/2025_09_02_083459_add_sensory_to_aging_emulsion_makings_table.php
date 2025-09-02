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
        Schema::table('aging_emulsion_makings', function (Blueprint $table) {
            $table->string('sensory_color')
                ->nullable()
                ->after('emulsion_result');
            $table->string('sensory_texture')
                ->nullable()
                ->after('sensory_color');
            $table->decimal('temp_after', 5, 2)
                ->nullable()
                ->after('sensory_texture');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aging_emulsion_makings', function (Blueprint $table) {
            $table->dropColumn('sensory_color');
            $table->dropColumn('sensory_texture');
            $table->dropColumn('temp_after');
        });
    }
};