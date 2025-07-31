<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;

class ReportLabSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'storage',
        'created_by',
        'known_by',
        'accepted_by',
        'approved_by',
        'approved_at',
    ];

    // Relasi ke detail_lab_samples
    public function details()
    {
        return $this->hasMany(DetailLabSample::class, 'report_uuid', 'uuid');
    }

    // Relasi ke area (jika ada tabel areas)
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}