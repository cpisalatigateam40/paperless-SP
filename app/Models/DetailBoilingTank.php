<?php
// app/Models/DetailBoilingTank.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DetailBoilingTank extends Model
{
    protected $fillable = [
        'uuid',
        'report_uuid',
        'kode_produksi',
        'start',
        'end',
        'suhu_adonan',
        'aktual_suhu_tangki_1',
        'aktual_suhu_tangki_2',
        'sensori_bentuk',
        'sensori_warna',
        'sensori_aroma',
        'sensori_rasa',
        'sensori_tekstur',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function report()
    {
        return $this->belongsTo(ReportBoilingTank::class, 'report_uuid', 'uuid');
    }

    public function checks()
    {
        return $this->hasMany(BoilingTankCheck::class, 'detail_uuid', 'uuid')
            ->orderBy('check_index');
    }
}