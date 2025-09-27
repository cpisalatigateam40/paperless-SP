<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailThermometer extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_thermometers';

    protected $fillable = [
        'uuid',
        'report_scale_uuid',
        'thermometer_uuid',
        'time_1',
        'time_2',
        'note',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'time_1' => 'datetime:H:i',
        'time_2' => 'datetime:H:i',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportScale::class, 'report_scale_uuid', 'uuid');
    }

    public function thermometer()
    {
        return $this->belongsTo(Thermometer::class, 'thermometer_uuid', 'uuid');
    }

    public function measurements()
    {
        return $this->hasMany(MeasurementThermometer::class, 'detail_thermometer_uuid', 'uuid');
    }
}