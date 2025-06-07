<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailGmpEmployee extends Model
{
    use HasFactory;

    protected $table = 'detail_gmp_employees';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'inspection_hour',
        'section_name',
        'employee_name',
        'notes',
        'corrective_action',
        'verification',
    ];

    public function report()
    {
        return $this->belongsTo(ReportGmpEmployee::class, 'report_uuid', 'uuid');
    }
}