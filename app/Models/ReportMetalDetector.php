<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportMetalDetector extends Model
{
    protected $table = 'report_metal_detectors';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'section_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
    ];

    public function details()
    {
        return $this->hasMany(DetailMetalDetector::class, 'report_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }
}