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
        Schema::create('detail_smoke_houses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('report_uuid');

            $table->uuid('master_uuid')->nullable();

            $table->uuid('product_uuid');

            $table->enum('machine_name', [
                'Fessmann',
                'Maurer',
                'Bastra',
                'Vemag',
            ]);

            $table->string('production_code');

            $table->decimal('gramase', 8, 2)->nullable();

            $table->unsignedInteger('smoke_house_no');

            $table->unsignedInteger('trolley_count');

            $table->unsignedInteger('stick_count');

            $table->dateTime('start_process')->nullable();
            $table->dateTime('end_process')->nullable();

            $table->dateTime('cooling_finish')->nullable();

            $table->text('documentation')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_smoke_houses');
    }
};
