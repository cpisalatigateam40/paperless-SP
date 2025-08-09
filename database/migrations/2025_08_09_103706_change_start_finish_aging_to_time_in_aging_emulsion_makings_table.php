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
        Schema::table('aging_emulsion_makings', function (Blueprint $table) {
            $table->time('start_aging')->nullable()->change();
            $table->time('finish_aging')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aging_emulsion_makings', function (Blueprint $table) {
            $table->string('start_aging')->nullable()->change();
            $table->string('finish_aging')->nullable()->change();
        });
    }
};