<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_waterbaths', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('report_uuid')->index();
            $table->string('batch_code', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_waterbaths');
    }
};