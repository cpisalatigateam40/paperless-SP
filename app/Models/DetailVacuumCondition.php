<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailVacuumCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'time',
        'production_code',
        'expired_date',
        'pack_quantity',
        'leaking_area_seal',
        'leaking_area_melipat',
        'leaking_area_casing',
        'leaking_area_other',
    ];

    // relasi ke report_vacuum_conditions
    public function report()
    {
        return $this->belongsTo(ReportVacuumCondition::class, 'report_uuid', 'uuid');
    }

    // relasi ke products
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}