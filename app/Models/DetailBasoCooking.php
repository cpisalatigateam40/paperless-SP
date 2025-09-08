<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailBasoCooking extends Model
{
    use HasFactory;

    protected $table = 'detail_baso_cookings';
    protected $fillable = [
        'uuid',
        'report_uuid',
        'production_code',
        'emulsion_temp',
        'boiling_tank_temp_1',
        'boiling_tank_temp_2',
        'initial_weight',
        'sensory_shape',
        'sensory_taste',
        'sensory_aroma',
        'sensory_texture',
        'sensory_color',
        'final_weight',
        'qc_paraf',
        'prod_paraf',
    ];

    // Relasi ke Report
    public function report()
    {
        return $this->belongsTo(ReportBasoCooking::class, 'report_uuid', 'uuid');
    }

    // Relasi ke BasoTemperatures
    public function temperatures()
    {
        return $this->hasMany(BasoTemperature::class, 'detail_uuid', 'uuid');
    }
}