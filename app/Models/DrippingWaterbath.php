<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DrippingWaterbath extends Model
{
    use HasFactory;

    protected $table = 'dripping_waterbaths';
    protected $fillable = [
        'uuid', 'report_uuid',
        'start_time_pasteur', 'stop_time_pasteur',
        'hot_zone_temperature', 'cold_zone_temperature', 'product_temp_final'
    ];

    public function report()
    {
        return $this->belongsTo(ReportWaterbath::class, 'report_uuid', 'uuid');
    }
}