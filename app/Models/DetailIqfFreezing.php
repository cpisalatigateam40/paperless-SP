<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailIqfFreezing extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'production_code',
        'best_before',
        'product_temp_before_iqf',
        'freezing_start_time',
        'freezing_duration',
        'room_temperature',
        'suction_temperature',
    ];

    // Relasi ke report
    public function report()
    {
        return $this->belongsTo(ReportIqfFreezing::class, 'report_uuid', 'uuid');
    }

    // Relasi ke produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}