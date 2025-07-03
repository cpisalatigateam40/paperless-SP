<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DetailChlorineResidue extends Model
{
    use HasFactory;

    protected $table = 'detail_chlorine_residues';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'day',
        'result_ppm',
        'remark',
        'corrective_action',
        'verification',
        'verified_by',
        'verified_at',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportChlorineResidue::class, 'report_uuid', 'uuid');
    }

    public function followups()
    {
        return $this->hasMany(FollowupChlorineResidue::class, 'detail_chlorine_residue_uuid', 'uuid');
    }
}