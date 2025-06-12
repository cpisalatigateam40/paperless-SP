<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportGmpEmployee extends Model
{
    use HasFactory;

    protected $table = 'report_gmp_employees';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailGmpEmployee::class, 'report_uuid', 'uuid');
    }

    public function sanitationCheck()
    {
        return $this->hasOne(SanitationCheck::class, 'report_gmp_employee_id', 'id');
    }

    public function sanitationAreas()
    {
        return $this->hasManyThrough(
            SanitationArea::class,
            SanitationCheck::class,
            'report_gmp_employee_id',
            'sanitation_check_uuid',
            'id',
            'uuid'
        );
    }
}