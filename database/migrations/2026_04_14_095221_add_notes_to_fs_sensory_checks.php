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
        Schema::table('fs_sensory_checks', function (Blueprint $table) {
            $table->text('ripeness_note')->nullable();
            $table->text('aroma_note')->nullable();
            $table->text('taste_note')->nullable();
            $table->text('texture_note')->nullable();
            $table->text('color_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fs_sensory_checks', function (Blueprint $table) {
            $table->dropColumn(['ripeness_note', 'aroma_note', 'texture_note', 'color_note', 'taste_note']);
        });
    }
};
