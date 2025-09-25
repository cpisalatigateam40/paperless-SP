<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoolingShockWaterbath extends Model
{
    use HasFactory;

    protected $table = 'cooling_shock_waterbaths';
    protected $fillable = [
        'uuid', 'report_uuid', 'initial_water_temp',
        'start_time_pasteur', 'stop_time_pasteur',
        'water_temp_setting', 'water_temp_actual', 'product_temp_final'
    ];

    public function report()
    {
        return $this->belongsTo(ReportWaterbath::class, 'report_uuid', 'uuid');
    }
}