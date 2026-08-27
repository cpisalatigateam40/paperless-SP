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
        Schema::create('boiling_tank_checks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_uuid');

            $table->unsignedInteger('check_index'); // urutan sample: 1,2,3,dst (default 3 di view, bisa ditambah)

            $table->decimal('berat_mentah', 8, 2)->nullable();       // Std 11-12 gr — diisi saat draft
            $table->decimal('actual_core_temp', 8, 2)->nullable();   // Std 12°C   — diisi saat draft
            $table->decimal('berat_matang', 8, 2)->nullable();       // diisi belakangan saat status -> selesai
            $table->decimal('suhu_after_cooling', 8, 2)->nullable(); // diisi belakangan saat status -> selesai

            $table->timestamps();

            $table->foreign('detail_uuid')
                ->references('uuid')->on('detail_boiling_tanks')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boiling_tank_checks');
    }
};
