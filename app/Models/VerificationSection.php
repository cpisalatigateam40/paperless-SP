<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VerificationSection extends Model
{
    use HasFactory;

    protected $table = 'verification_sections';

    protected $fillable = [
        'uuid', 'report_uuid', 'section_uuid',
        'condition', 'corrective_action', 'verification',
    ];

    public function report()
    {
        return $this->belongsTo(ReportProductChange::class, 'report_uuid', 'uuid');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }
}