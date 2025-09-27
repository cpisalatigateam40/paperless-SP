<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class SanitationResult extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'sanitation_results';

    protected $fillable = [
        'sanitation_area_uuid',
        'hour_to',
        'chlorine_level',
        'temperature',
        'notes',
        'corrective_action'
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function sanitationArea()
    {
        return $this->belongsTo(SanitationArea::class, 'sanitation_area_uuid', 'uuid');
    }
}