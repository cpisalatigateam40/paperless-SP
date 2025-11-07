<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class CoolingShockWaterbath extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'cooling_shock_waterbaths';
    protected $fillable = [
        'uuid', 'report_uuid', 'initial_water_temp',
        'start_time_pasteur', 'stop_time_pasteur',
        'water_temp_setting', 'water_temp_actual', 'product_temp_final', 'water_temp_final'
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function report()
    {
        return $this->belongsTo(ReportWaterbath::class, 'report_uuid', 'uuid');
    }
}