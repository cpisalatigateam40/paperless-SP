<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class FollowupSanitation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'followup_sanitations';

    protected $fillable = [
        'sanitation_area_uuid',
        'notes',
        'action',
        'verification',
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function sanitationArea()
    {
        return $this->belongsTo(SanitationArea::class, 'sanitation_area_uuid', 'uuid');
    }
}