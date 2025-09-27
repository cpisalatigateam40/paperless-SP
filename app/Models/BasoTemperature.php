<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class BasoTemperature extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'baso_temperatures';
    protected $fillable = [
        'uuid',
        'detail_uuid',
        'time_type',
        'time_recorded',
        'baso_temp_1',
        'baso_temp_2',
        'baso_temp_3',
        'baso_temp_4',
        'baso_temp_5',
        'avg_baso_temp',
    ];

    protected $auditEvents = [
        'updated',
    ];

    // Relasi ke Detail
    public function detail()
    {
        return $this->belongsTo(DetailBasoCooking::class, 'detail_uuid', 'uuid');
    }
}