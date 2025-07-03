<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupChlorineResidue extends Model
{
    use HasFactory;

    protected $table = 'followup_chlorine_residues';

    protected $fillable = [
        'detail_chlorine_residue_uuid',
        'notes',
        'corrective_action',
        'verification',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailChlorineResidue::class, 'detail_chlorine_residue_uuid', 'uuid');
    }
}