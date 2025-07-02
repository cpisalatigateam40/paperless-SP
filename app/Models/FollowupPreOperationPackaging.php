<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupPreOperationPackaging extends Model
{
    use HasFactory;

    protected $table = 'followup_pre_operation_packagings';

    protected $fillable = [
        'pre_operation_packaging_uuid',
        'notes',
        'corrective_action',
        'verification',
    ];

    public function preOperationPackaging()
    {
        return $this->belongsTo(PreOperationPackaging::class, 'pre_operation_packaging_uuid', 'uuid');
    }
}