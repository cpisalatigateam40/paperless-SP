<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class SanitationArea extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'sanitation_areas';

    protected $fillable =
        ['uuid', 'sanitation_check_uuid', 'area_name', 'chlorine_std', 'notes', 'corrective_action', 'verification'];

    protected $auditEvents = [
        'updated',
    ];


    public function sanitationCheck()
    {
        return $this->belongsTo(SanitationCheck::class, 'sanitation_check_uuid', 'uuid');
    }

    public function sanitationResult()
    {
        return $this->hasMany(SanitationResult::class, 'sanitation_area_uuid', 'uuid');
    }

    public function followups()
    {
        return $this->hasMany(FollowupSanitation::class, 'sanitation_area_uuid', 'uuid');
    }
}