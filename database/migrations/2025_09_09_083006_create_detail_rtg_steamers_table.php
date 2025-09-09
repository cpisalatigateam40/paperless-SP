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
        Schema::create('detail_rtg_steamers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid');

            // header detail
            $table->string('steamer')->nullable();
            $table->string('production_code')->nullable();
            $table->integer('trolley_count')->nullable();

            // steaming
            $table->decimal('room_temp', 5, 2)->nullable();
            $table->decimal('product_temp', 5, 2)->nullable();
            $table->integer('time_minute')->nullable();

            // lama proses
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // sensori
            $table->string('sensory_ripeness')->nullable();
            $table->string('sensory_taste')->nullable();
            $table->string('sensory_aroma')->nullable();
            $table->string('sensory_texture')->nullable();
            $table->string('sensory_color')->nullable();

            // paraf
            $table->string('qc_paraf')->nullable();
            $table->string('production_paraf')->nullable();
            $table->timestamps();

            $table->foreign('report_uuid')
                ->references('uuid')
                ->on('report_rtg_steamers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_rtg_steamers');
    }
};