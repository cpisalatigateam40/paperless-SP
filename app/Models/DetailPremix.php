<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DetailPremix extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'premix_uuid',
        'weight',
        'used_for_batch',
        'notes',
        'corrective_action',
        'verification',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportPremix::class, 'report_uuid', 'uuid');
    }

    public function premix()
    {
        return $this->belongsTo(Premix::class, 'premix_uuid', 'uuid');
    }
}