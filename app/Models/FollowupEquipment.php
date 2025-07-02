<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupEquipment extends Model
{
    use HasFactory;

    protected $table = 'followup_equipments';

    protected $fillable = [
        'verification_equipment_uuid',
        'notes',
        'corrective_action',
        'verification',
    ];

    public function verificationEquipment()
    {
        return $this->belongsTo(VerificationEquipment::class, 'verification_equipment_uuid', 'uuid');
    }
}