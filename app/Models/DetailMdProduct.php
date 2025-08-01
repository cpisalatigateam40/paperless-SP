<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailMdProduct extends Model
{
    protected $table = 'detail_md_products';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'time',
        'production_code',
        'gramase',
        'best_before',
        'program_number',
        'corrective_action',
        'verification',
    ];

    protected $casts = [
        'time' => 'datetime:H:i',
        'verification' => 'boolean',
    ];

    public function report()
    {
        return $this->belongsTo(ReportMdProduct::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function positions()
    {
        return $this->hasMany(PositionMdProduct::class, 'detail_uuid', 'uuid');
    }
}