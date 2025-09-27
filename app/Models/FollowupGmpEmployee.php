<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class FollowupGmpEmployee extends Model implements Auditable

{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'followup_gmp_employee';
    
    protected $fillable = [
        'gmp_employee_detail_id',
        'notes',
        'action',
        'verification',
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailGmpEmployee::class, 'gmp_employee_detail_id');
    }
}