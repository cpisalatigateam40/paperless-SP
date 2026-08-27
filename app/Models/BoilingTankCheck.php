<?php
// app/Models/BoilingTankCheck.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BoilingTankCheck extends Model
{
    protected $fillable = [
        'uuid',
        'detail_uuid',
        'check_index',
        'berat_mentah',
        'actual_core_temp',
        'berat_matang',
        'suhu_after_cooling',
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

    public function detail()
    {
        return $this->belongsTo(DetailBoilingTank::class, 'detail_uuid', 'uuid');
    }
}