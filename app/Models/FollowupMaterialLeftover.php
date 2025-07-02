<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupMaterialLeftover extends Model
{
    use HasFactory;

    protected $table = 'followup_material_leftovers';

    protected $fillable = [
        'material_leftover_uuid',
        'notes',
        'corrective_action',
        'verification',
    ];

    // Relasi ke VerificationMaterialLeftover
    public function materialLeftover()
    {
        return $this->belongsTo(VerificationMaterialLeftover::class, 'material_leftover_uuid', 'uuid');
    }
}