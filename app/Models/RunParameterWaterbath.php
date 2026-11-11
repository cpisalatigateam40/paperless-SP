<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RunParameterWaterbath extends Model
{
    use HasFactory;

    protected $table = 'run_parameter_waterbaths';
    protected $fillable = [
        'uuid', 'run_uuid', 'parameter',
        'set_temp_min', 'set_temp_max',
        'cold_zone_set_min', 'cold_zone_set_max',
        'set_time', 'std_ct_min', 'std_ct_max',
        'act_temp', 'cold_zone_act', 'act_time', 'act_ct',
    ];

    protected $casts = [
        'set_temp_min'      => 'decimal:2',
        'set_temp_max'      => 'decimal:2',
        'cold_zone_set_min' => 'decimal:2',
        'cold_zone_set_max' => 'decimal:2',
        'set_time'          => 'decimal:2',
        'std_ct_min'        => 'decimal:2',
        'std_ct_max'        => 'decimal:2',
        'act_temp'          => 'decimal:2',
        'cold_zone_act'     => 'decimal:2',
        'act_time'          => 'decimal:2',
        'act_ct'            => 'decimal:2',
    ];


    public function run()
    {
        return $this->belongsTo(RunWaterbath::class, 'run_uuid', 'uuid');
    }

    public function getLabelAttribute(): string
    {
        return MasterWaterbathParameter::PARAMETERS[$this->parameter] ?? $this->parameter;
    }

    public function isDrying(): bool
    {
        return $this->parameter === MasterWaterbathParameter::DRYING;
    }

    public function getSetTempRangeAttribute(): string
    {
        return MasterWaterbathParameter::formatRange($this->set_temp_min, $this->set_temp_max);
    }

    public function getColdZoneSetRangeAttribute(): string
    {
        return MasterWaterbathParameter::formatRange($this->cold_zone_set_min, $this->cold_zone_set_max);
    }

    public function getStdCtRangeAttribute(): string
    {
        return MasterWaterbathParameter::formatRange($this->std_ct_min, $this->std_ct_max);
    }

    public function isActTempOk(): ?bool
    {
        return MasterWaterbathParameter::inRange($this->act_temp, $this->set_temp_min, $this->set_temp_max);
    }

    public function isColdZoneActOk(): ?bool
    {
        return MasterWaterbathParameter::inRange($this->cold_zone_act, $this->cold_zone_set_min, $this->cold_zone_set_max);
    }

    public function isActCtOk(): ?bool
    {
        return MasterWaterbathParameter::inRange($this->act_ct, $this->std_ct_min, $this->std_ct_max);
    }

    public function isActTimeOk(): ?bool
    {
        return MasterWaterbathParameter::inRange($this->act_time, $this->set_time, null);
    }
}