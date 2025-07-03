<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DetailProductionNonconformity extends Model
{
    use HasFactory;

    protected $table = 'detail_production_nonconformities';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'occurrence_time',
        'description',
        'quantity',
        'hazard_category',
        'disposition',
        'remark',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportProductionNonconformity::class, 'report_uuid', 'uuid');
    }
}