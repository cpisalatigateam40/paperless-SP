<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SanitationResult extends Model
{
    use HasFactory;

    protected $table = 'sanitation_results';

    protected $fillable = [
        'sanitation_area_uuid',
        'hour_to',
        'chlorine_level',
        'temperature',
        'notes',
        'corrective_action'
    ];

    public function sanitationArea()
    {
        return $this->belongsTo(SanitationArea::class, 'sanitation_area_uuid', 'uuid');
    }
}