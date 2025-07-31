<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;

class SanitationCheck extends Model
{
    use HasFactory;

    protected $table = 'sanitation_checks';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'hour_1',
        'hour_2',
        'verification',
        'report_gmp_employee_id'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function sanitationArea()
    {
        return $this->hasMany(SanitationArea::class, 'sanitation_check_uuid', 'uuid');
    }

    public function report()
    {
        return $this->belongsTo(ReportGmpEmployee::class, 'report_gmp_employee_id');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}