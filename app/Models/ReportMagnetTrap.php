<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;

class ReportMagnetTrap extends Model
{
    use HasFactory;

    protected $table = 'report_magnet_traps';

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

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? Str::uuid()->toString();
        });
        static::addGlobalScope(new UserAreaScope);
    }

    // Relasi ke Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    // Relasi ke Section
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }

    // Relasi ke Detail
    public function details()
    {
        return $this->hasMany(DetailMagnetTrap::class, 'report_uuid', 'uuid');
    }
}