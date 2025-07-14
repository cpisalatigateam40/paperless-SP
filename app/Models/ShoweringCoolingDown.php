<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ShoweringCoolingDown extends Model
{
    use HasFactory;

    protected $table = 'showering_cooling_downs';

    protected $fillable = [
        'uuid',
        'report_detail_uuid',
        'showering_time',
        'room_temp_1',
        'room_temp_2',
        'product_temp_1',
        'product_temp_2',
        'time_minutes_1',
        'time_minutes_2',
        'product_temp_after_exit_1',
        'product_temp_after_exit_2',
        'product_temp_after_exit_3',
        'avg_product_temp_after_exit'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function detail()
    {
        return $this->belongsTo(DetailMaurerCooking::class, 'report_detail_uuid', 'uuid');
    }
}