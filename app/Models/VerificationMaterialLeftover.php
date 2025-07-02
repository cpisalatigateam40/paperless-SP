<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VerificationMaterialLeftover extends Model
{
    use HasFactory;

    protected $table = 'verification_material_leftovers';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'item',
        'condition',
        'corrective_action',
        'verification',
    ];

    public function report()
    {
        return $this->belongsTo(ReportProductChange::class, 'report_uuid', 'uuid');
    }

    public function followups()
    {
        return $this->hasMany(FollowUpMaterialLeftover::class, 'material_leftover_uuid', 'uuid');
    }
}