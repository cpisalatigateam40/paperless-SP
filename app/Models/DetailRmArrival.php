<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class DetailRmArrival extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'raw_material_uuid',
        'material_uuid',
        'material_type',
        'supplier',
        'rm_condition',
        'time',
        'production_code',
        'temperature',
        'packaging_condition',
        'sensorial_condition',
        'sensory_appearance',
        'sensory_aroma',
        'sensory_color',
        'contamination',
        'problem',
        'corrective_action',
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function report()
    {
        return $this->belongsTo(ReportRmArrival::class, 'report_uuid', 'uuid');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_uuid', 'uuid');
    }


    public function premix()
    {
        return $this->belongsTo(Premix::class, 'material_uuid', 'uuid');
    }
}