<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupDetailEquipmentCleanliness extends Model
{
    use HasFactory;

    protected $table = 'followup_detail_equipment_cleanliness';

    protected $fillable = [
        'detail_equipment_uuid',
        'notes',
        'corrective_action',
        'verification'
    ];

    public function detail()
    {
        return $this->belongsTo(DetailEquipmentCleanliness::class, 'detail_equipment_uuid', 'uuid');
    }
}