<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailSolvent extends Model
{
    use HasFactory;

    protected $table = 'detail_solvents';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'solvent_uuid',
        'verification_result',
        'corrective_action',
        'reverification_action',
    ];

    public function report()
    {
        return $this->belongsTo(ReportSolvent::class, 'report_uuid', 'uuid');
    }

    public function solvent()
    {
        return $this->belongsTo(SolventItem::class, 'solvent_uuid', 'uuid');
    }
}