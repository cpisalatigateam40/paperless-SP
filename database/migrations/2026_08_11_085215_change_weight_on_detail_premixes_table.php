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
        Schema::table('detail_premixes', function (Blueprint $table) {
            $table->decimal('weight', 12, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_premixes', function (Blueprint $table) {
            $table->decimal('weight', 12, 3)->change();
        });
    }
};
