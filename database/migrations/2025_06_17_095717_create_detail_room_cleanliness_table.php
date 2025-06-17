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
        Schema::create('detail_room_cleanliness', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_re_uuid')->nullable();
            $table->uuid('room_uuid')->nullable();
            $table->uuid('room_element_uuid')->nullable();
            $table->string('condition')->nullable(); // Bersih / Kotor
            $table->text('notes')->nullable(); // Keterangan jika kotor
            $table->text('corrective_action')->nullable()->nullable();
            $table->text('verification')->nullable()->nullable();
            $table->timestamps();

            $table->foreign('report_re_uuid')->references('uuid')->on('report_re_cleanliness')->onDelete('cascade');
            $table->foreign('room_uuid')->references('uuid')->on('rooms')->onDelete('restrict');
            $table->foreign('room_element_uuid')->references('uuid')->on('room_elements')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_room_cleanliness');
    }
};