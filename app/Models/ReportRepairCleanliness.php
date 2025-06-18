<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportRepairCleanliness extends Model
{
    use HasFactory;

    protected $table = 'report_repair_cleanliness';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailRepairCleanliness::class, 'report_uuid', 'uuid');
    }
}