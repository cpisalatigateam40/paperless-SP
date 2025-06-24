<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportSharpTool extends Model
{
    use HasFactory;

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

    /**
     * Relasi ke area berdasarkan UUID
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    /**
     * Relasi ke detail (banyak)
     */
    public function details()
    {
        return $this->hasMany(DetailSharpTool::class, 'report_uuid', 'uuid');
    }
}