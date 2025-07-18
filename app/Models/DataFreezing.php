<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DataFreezing extends Model
{
    use HasFactory;

    protected $table = 'data_freezings';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'start_product_temp',
        'end_product_temp',
        'iqf_room_temp',
        'iqf_suction_temp',
        'freezing_time_display',
        'freezing_time_actual',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function detail()
    {
        return $this->belongsTo(DetailFreezPackaging::class, 'detail_uuid', 'uuid');
    }
}