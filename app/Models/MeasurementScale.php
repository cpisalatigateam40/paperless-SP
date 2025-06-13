<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class MeasurementScale extends Model
{
    use HasFactory;

    protected $table = 'measurement_scales';

    protected $fillable = [
        'uuid',
        'detail_scale_uuid',
        'inspection_time_index',
        'standard_weight',
        'measured_value',
    ];

    protected static function booted()
    {
        static::creating(function ($scale) {
            $scale->uuid = (string) Str::uuid();
        });
    }

    public function detailScale()
    {
        return $this->belongsTo(DetailScale::class, 'detail_scale_uuid');
    }
}