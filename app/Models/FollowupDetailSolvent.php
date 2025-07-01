<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupDetailSolvent extends Model
{
    use HasFactory;

    protected $table = 'followup_detail_solvents';

    protected $fillable = [
        'detail_solvent_uuid',
        'notes',
        'corrective_action',
        'verification',
    ];

    // Relasi ke detail solvent
    public function detailSolvent()
    {
        return $this->belongsTo(DetailSolvent::class, 'detail_solvent_uuid', 'uuid');
    }
}