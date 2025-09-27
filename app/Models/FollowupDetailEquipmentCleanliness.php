<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class FollowupDetailEquipmentCleanliness extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'followup_detail_equipment_cleanliness';

    protected $fillable = [
        'detail_equipment_uuid',
        'notes',
        'corrective_action',
        'verification'
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailEquipmentCleanliness::class, 'detail_equipment_uuid', 'uuid');
    }
}