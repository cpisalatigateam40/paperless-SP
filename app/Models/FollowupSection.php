<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupSection extends Model
{
    use HasFactory;

    protected $table = 'followup_sections';

    protected $fillable = [
        'verification_section_uuid',
        'notes',
        'corrective_action',
        'verification',
    ];

    public function verificationSection()
    {
        return $this->belongsTo(VerificationSection::class, 'verification_section_uuid', 'uuid');
    }
}