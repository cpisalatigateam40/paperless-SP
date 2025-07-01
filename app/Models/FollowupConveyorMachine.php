<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupConveyorMachine extends Model
{
    use HasFactory;

    protected $table = 'followup_conveyor_machines';

    protected $fillable = [
        'conveyor_machine_uuid',
        'notes',
        'corrective_action',
        'verification',
    ];

    public function machine()
    {
        return $this->belongsTo(ConveyorMachine::class, 'conveyor_machine_uuid', 'uuid');
    }
}