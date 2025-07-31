<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;

class ReportVacuumCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'accepted_by',
        'approved_by',
        'approved_at',
    ];

    // relasi ke detail_vacuum_conditions
    public function details()
    {
        return $this->hasMany(DetailVacuumCondition::class, 'report_uuid', 'uuid');
    }

    // relasi ke area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}