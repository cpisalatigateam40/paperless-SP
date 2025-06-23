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
        Schema::create('detail_magnet_traps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->nullable();
            $table->time('time')->nullable();
            $table->enum('source', ['QC', 'Produksi'])->nullable();
            $table->text('finding')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')->references('uuid')->on('report_magnet_traps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_magnet_traps');
    }
};