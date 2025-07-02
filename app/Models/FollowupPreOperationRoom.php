<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupPreOperationRoom extends Model
{
    use HasFactory;

    protected $table = 'followup_pre_operation_rooms';

    protected $fillable = [
        'pre_operation_room_uuid',
        'notes',
        'corrective_action',
        'verification',
    ];

    public function preOperationRoom()
    {
        return $this->belongsTo(PreOperationRoom::class, 'pre_operation_room_uuid', 'uuid');
    }
}