<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailScale extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_scales';

    protected $fillable = [
        'uuid',
        'report_scale_uuid',
        'scale_uuid',
        'time_1',
        'time_2',
        'notes',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function booted()
    {
        static::creating(function ($scale) {
            $scale->uuid = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportScale::class, 'report_scale_uuid', 'uuid');
    }

    public function scale()
    {
        return $this->belongsTo(Scale::class, 'scale_uuid', 'uuid');
    }

    public function measurements()
    {
        return $this->hasMany(MeasurementScale::class, 'detail_scale_uuid', 'uuid');
    }
}