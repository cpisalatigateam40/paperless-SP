<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class StandardStep extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'standard_steps';

    protected $fillable = [
        'uuid',
        'step_uuid',
        'start_time',
        'end_time',
        'water_temp',
        'pressure',
        'set_time',
        'set_water_temp_min',
        'set_water_temp_max',
        'set_pressure_min',
        'set_pressure_max',
        'act_time',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function step()
    {
        return $this->belongsTo(StepPasteur::class, 'step_uuid', 'uuid');
    }
}