<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailRtgSteamer extends Model
{
    use HasFactory;

    protected $table = 'detail_rtg_steamers';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'steamer',
        'production_code',
        'trolley_count',
        'room_temp',
        'product_temp',
        'time_minute',
        'start_time',
        'end_time',
        'sensory_ripeness',
        'sensory_taste',
        'sensory_aroma',
        'sensory_texture',
        'sensory_color',
        'qc_paraf',
        'production_paraf',
    ];

    // Relasi ke Report
    public function report()
    {
        return $this->belongsTo(ReportRtgSteamer::class, 'report_uuid', 'uuid');
    }
}