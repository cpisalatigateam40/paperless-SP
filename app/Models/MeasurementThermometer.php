<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class MeasurementThermometer extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'measurement_thermometers';

    protected $fillable = [
        'uuid',
        'detail_thermometer_uuid',
        'inspection_time_index',
        'standard_temperature',
        'measured_value',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'measured_value' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function detail()
    {
        return $this->belongsTo(DetailThermometer::class, 'detail_thermometer_uuid', 'uuid');
    }
}