<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class PasteurisasiWaterbath extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'pasteurisasi_waterbaths';
    protected $fillable = [
        'uuid', 'report_uuid', 'initial_product_temp', 'initial_water_temp',
        'start_time_pasteur', 'stop_time_pasteur',
        'water_temp_after_input_panel', 'water_temp_after_input_actual',
        'water_temp_setting', 'water_temp_actual', 'water_temp_final', 'product_temp_final'
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function report()
    {
        return $this->belongsTo(ReportWaterbath::class, 'report_uuid', 'uuid');
    }
}