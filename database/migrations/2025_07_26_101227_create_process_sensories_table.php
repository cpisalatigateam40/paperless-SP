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
        Schema::create('process_sensories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_uuid')->nullable();
            $table->string('homogeneous')->nullable();
            $table->string('stiffness')->nullable();
            $table->string('aroma')->nullable();
            $table->string('foreign_object')->nullable();
            $table->timestamps();

            $table->foreign('detail_uuid')->references('uuid')->on('detail_process_prods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_sensories');
    }
};