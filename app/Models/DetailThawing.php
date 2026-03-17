<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class DetailThawing extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_thawings';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'raw_material_uuid',
        'start_thawing_time',
        'end_thawing_time',
        'package_condition',
        'production_code',
        'qty',
        'room_condition',
        'inspection_time',
        'room_temp',
        'water_temp',
        'product_temp',
        'product_condition',
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function report()
    {
        return $this->belongsTo(ReportThawing::class, 'report_uuid', 'uuid');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_uuid', 'uuid');
    }
}
