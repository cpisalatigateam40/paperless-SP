<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailMetalDetector extends Model
{
    protected $table = 'detail_metal_detectors';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'hour',
        'production_code',
        'result_fe',
        'result_non_fe',
        'result_sus316',
        'verif_loma',
        'nonconformity',
        'corrective_action',
        'verif_after_correct',
        'notes',
    ];

    public function report()
    {
        return $this->belongsTo(ReportMetalDetector::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}