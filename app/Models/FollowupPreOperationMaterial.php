<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupPreOperationMaterial extends Model
{
    use HasFactory;

    protected $table = 'followup_pre_operation_materials';

    protected $fillable = [
        'pre_operation_material_uuid',
        'notes',
        'corrective_action',
        'verification',
    ];

    public function preOperationMaterial()
    {
        return $this->belongsTo(PreOperationMaterial::class, 'pre_operation_material_uuid', 'uuid');
    }
}