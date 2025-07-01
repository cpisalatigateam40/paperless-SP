<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DetailMagnetTrap extends Model
{
    use HasFactory;

    protected $table = 'detail_magnet_traps';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'time',
        'source',
        'finding_image',
        'note',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? Str::uuid()->toString();
        });
    }

    // Relasi ke Report Magnet Trap
    public function report()
    {
        return $this->belongsTo(ReportMagnetTrap::class, 'report_uuid', 'uuid');
    }
}