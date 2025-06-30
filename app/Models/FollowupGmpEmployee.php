<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowupGmpEmployee extends Model

{

    protected $table = 'followup_gmp_employee';
    
    protected $fillable = [
        'gmp_employee_detail_id',
        'notes',
        'action',
        'verification',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailGmpEmployee::class, 'gmp_employee_detail_id');
    }
}