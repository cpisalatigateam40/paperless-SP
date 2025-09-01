<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailRmArrival extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'raw_material_uuid',
        'supplier',
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

    public function report()
    {
        return $this->belongsTo(ReportRmArrival::class, 'report_uuid', 'uuid');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_uuid', 'uuid');
    }
}