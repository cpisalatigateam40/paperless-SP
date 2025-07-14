<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ShProcessStep extends Model
{
    use HasFactory;

    protected $table = 'sh_process_steps';

    protected $fillable = [
        'uuid',
        'report_detail_uuid',
        'step_name',
        'room_temperature_1',
        'room_temperature_2',
        'rh_1',
        'rh_2',
        'time_minutes_1',
        'time_minutes_2',
        'product_temperature_1',
        'product_temperature_2'
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