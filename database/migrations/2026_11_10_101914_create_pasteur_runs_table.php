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
        Schema::create('pasteur_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('detail_uuid')->index();
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->string('retort_chamber_number', 20)->nullable();
            $table->unsignedSmallInteger('trolley_count')->nullable();
            $table->unsignedSmallInteger('tray_count')->nullable();

            // Jam proses (satu kali per run)
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Aktual + snapshot standar dari master (untuk warna merah)
            $table->decimal('initial_temp', 6, 2)->nullable();
            $table->decimal('initial_temp_min', 6, 2)->nullable();
            $table->decimal('initial_temp_max', 6, 2)->nullable();

            $table->boolean('sensor_ok')->nullable();   // null = belum diisi
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['detail_uuid', 'attempt']);
        });

        // 2. Step terhubung ke run (null = data lama)
        Schema::table('step_pasteurs', function (Blueprint $table) {
            $table->uuid('run_uuid')->nullable()->index()->after('detail_uuid');
        });

        // 3. Snapshot standar per step
        Schema::table('standard_steps', function (Blueprint $table) {
            $table->decimal('set_time', 7, 2)->nullable();
            $table->decimal('set_water_temp_min', 6, 2)->nullable();
            $table->decimal('set_water_temp_max', 6, 2)->nullable();
            $table->decimal('set_pressure_min', 6, 3)->nullable();
            $table->decimal('set_pressure_max', 6, 3)->nullable();
            $table->decimal('act_time', 7, 2)->nullable()->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('standard_steps', function (Blueprint $table) {
            $table->dropColumn([
                'set_time', 'set_water_temp_min', 'set_water_temp_max',
                'set_pressure_min', 'set_pressure_max', 'act_time'
            ]);
        });

        Schema::table('step_pasteurs', function (Blueprint $table) {
            $table->dropColumn('run_uuid');
        });

        Schema::dropIfExists('pasteur_runs');
    }
};
