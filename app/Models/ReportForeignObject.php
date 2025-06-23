<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportForeignObject extends Model
{
    use HasFactory;

    protected $table = 'report_foreign_objects';

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

    protected $casts = [
        'approved_at' => 'datetime',
        'date' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(DetailForeignObject::class, 'report_uuid', 'uuid');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }
}