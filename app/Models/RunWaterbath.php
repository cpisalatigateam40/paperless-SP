<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RunWaterbath extends Model
{
    use HasFactory;

    protected $table = 'run_waterbaths';
    protected $fillable = [
        'uuid', 'report_uuid', 'run_number', 'waterbath_number',
        'initial_temp', 'initial_temp_min', 'initial_temp_max',
        'sensor_ok', 'notes',
    ];

    protected $casts = [
        'run_number'       => 'integer',
        'initial_temp'     => 'decimal:2',
        'initial_temp_min' => 'decimal:2',
        'initial_temp_max' => 'decimal:2',
        'sensor_ok'        => 'boolean',
    ];


    public function report()
    {
        return $this->belongsTo(ReportWaterbath::class, 'report_uuid', 'uuid');
    }

    public function parameters()
    {
        return $this->hasMany(RunParameterWaterbath::class, 'run_uuid', 'uuid');
    }

    public function parameter(string $name): ?RunParameterWaterbath
    {
        return $this->parameters->firstWhere('parameter', $name);
    }

    public function getIsRepasteurAttribute(): bool
    {
        return $this->run_number > 1;
    }

    public function getInitialTempRangeAttribute(): string
    {
        return MasterWaterbathParameter::formatRange($this->initial_temp_min, $this->initial_temp_max);
    }

    // true = di dalam range, false = di luar (merah), null = tidak bisa dinilai
    public function isInitialTempOk(): ?bool
    {
        return MasterWaterbathParameter::inRange(
            $this->initial_temp, $this->initial_temp_min, $this->initial_temp_max
        );
    }

    public function steps()
    {
        return $this->hasMany(RunStepWaterbath::class, 'run_uuid', 'uuid');
    }

    public function step(string $name): ?RunStepWaterbath
    {
        return $this->steps->firstWhere('parameter', $name);
    }
}