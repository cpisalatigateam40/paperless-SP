<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VerificationEquipment extends Model
{
    use HasFactory;

     protected $table = 'verification_equipments';

    protected $fillable = [
        'uuid', 'report_uuid', 'equipment_uuid',
        'condition', 'corrective_action', 'verification',
    ];

    public function report()
    {
        return $this->belongsTo(ReportProductChange::class, 'report_uuid', 'uuid');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_uuid', 'uuid');
    }
}