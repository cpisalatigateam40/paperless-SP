<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailProductionNonconformity extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_production_nonconformities';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'occurrence_time',
        'description',
        'quantity',
        'hazard_category',
        'disposition',
        'evidence',
        'remark',
        'unit',
    ];

    protected $auditEvents = [
        'updated',
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