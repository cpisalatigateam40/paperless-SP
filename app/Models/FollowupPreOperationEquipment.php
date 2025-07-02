<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupPreOperationEquipment extends Model
{
    use HasFactory;

    protected $table = 'followup_pre_operation_equipments';

    protected $fillable = [
        'pre_operation_equipment_uuid',
        'notes',
        'corrective_action',
        'verification',
    ];

    public function preOperationEquipment()
    {
        return $this->belongsTo(PreOperationEquipment::class, 'pre_operation_equipment_uuid', 'uuid');
    }
}