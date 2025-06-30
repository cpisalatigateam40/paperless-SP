<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowupSanitation extends Model
{
    protected $table = 'followup_sanitations';

    protected $fillable = [
        'sanitation_area_uuid',
        'notes',
        'action',
        'verification',
    ];

    public function sanitationArea()
    {
        return $this->belongsTo(SanitationArea::class, 'sanitation_area_uuid', 'uuid');
    }
}