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
        Schema::create('detail_boiling_tanks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid');

            $table->string('kode_produksi')->nullable();
            $table->time('start')->nullable();
            $table->time('end')->nullable();

            $table->decimal('suhu_adonan', 8, 2)->nullable();          // Std 16 ± 2°C
            $table->decimal('aktual_suhu_tangki_1', 8, 2)->nullable(); // Std 75-85°C
            $table->decimal('aktual_suhu_tangki_2', 8, 2)->nullable(); // Std 85-95°C

            $table->string('sensori_bentuk')->nullable();
            $table->string('sensori_warna')->nullable();
            $table->string('sensori_aroma')->nullable();
            $table->string('sensori_rasa')->nullable();
            $table->string('sensori_tekstur')->nullable();

            $table->timestamps();

            $table->foreign('report_uuid')
                ->references('uuid')->on('report_boiling_tanks')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_boiling_tanks');
    }
};
