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
        Schema::table('detail_lab_samples', function (Blueprint $table) {
            $table->string('sample_type')->nullable()->change();
            $table->string('unit')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_lab_samples', function (Blueprint $table) {
            $table->string('sample_type')->nullable(false)->change();
            $table->string('unit')->nullable(false)->change();
        });
    }
};
