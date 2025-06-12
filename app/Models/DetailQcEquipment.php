<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailQcEquipment extends Model
{
    use HasFactory;

    protected $table = 'detail_qc_equipments';

    protected $fillable = [
        'uuid',
        'report_qc_equipment_uuid',
        'qc_equipment_uuid',
        'actual_quantity',
        'time_start',
        'time_end',
        'notes',
    ];

    public function report()
    {
        return $this->belongsTo(ReportQcEquipment::class, 'report_qc_equipment_uuid', 'uuid');
    }

    public function item()
    {
        return $this->belongsTo(QcEquipment::class, 'qc_equipment_uuid', 'uuid');
    }
}