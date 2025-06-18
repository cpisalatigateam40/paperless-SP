<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConveyorMachine extends Model
{
    use HasFactory;

    protected $table = 'conveyor_machines';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'machine_name',
        'time',
        'status',
        'qc_check',
        'kr_check',
        'notes',
        'corrective_action',
        'verification',
    ];

    protected $keyType = 'int';
    public $incrementing = true;

    public function report()
    {
        return $this->belongsTo(ReportConveyorCleanliness::class, 'report_uuid', 'uuid');
    }
}