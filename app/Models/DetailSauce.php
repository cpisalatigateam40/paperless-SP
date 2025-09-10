<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailSauce extends Model
{
    use HasFactory;

    protected $table = 'detail_sauces';
    protected $fillable = [
        'uuid',
        'report_uuid',
        'time',
        'process_step',
        'duration',
        'mixing_paddle_on',
        'mixing_paddle_off',
        'pressure',
        'target_temperature',
        'actual_temperature',
        'color',
        'aroma',
        'taste',
        'texture',
        'notes',
    ];

    // Relasi ke Header Report
    public function report()
    {
        return $this->belongsTo(ReportSauce::class, 'report_uuid', 'uuid');
    }

    public function rawMaterials()
    {
        return $this->hasMany(RmSauce::class, 'detail_uuid', 'uuid');
    }
}