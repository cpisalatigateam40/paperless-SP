<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportRepackVerif extends Model
{
    use HasFactory;

    protected $table = 'report_repack_verifs';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
    ];

    // Relasi ke area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    // Relasi ke detail repack
    public function details()
    {
        return $this->hasMany(DetailRepackVerif::class, 'report_uuid', 'uuid');
    }
}