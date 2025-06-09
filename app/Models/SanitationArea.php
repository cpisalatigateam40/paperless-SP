<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SanitationArea extends Model
{
    use HasFactory;

    protected $table = 'sanitation_areas';

    protected $fillable =
        ['uuid', 'sanitation_check_uuid', 'area_name', 'chlorine_std', 'notes', 'corrective_action'];

    public function sanitationCheck()
    {
        return $this->belongsTo(SanitationCheck::class, 'sanitation_check_uuid', 'uuid');
    }

    public function sanitationResult()
    {
        return $this->hasMany(SanitationResult::class, 'sanitation_area_uuid', 'uuid');
    }
}