<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class FsCoolingDown extends Model
{
    use HasFactory;

    protected $table = 'fs_cooling_downs';

    protected $fillable = [
        'uuid',
        'report_detail_uuid',
        'step_name',
        'time_minutes_1',
        'time_minutes_2',
        'rh_1',
        'rh_2',
        'product_temp_after_exit_1',
        'product_temp_after_exit_2',
        'product_temp_after_exit_3',
        'avg_product_temp_after_exit',
        'raw_weight',
        'cooked_weight',
        'loss_kg',
        'loss_percent',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function detail()
    {
        return $this->belongsTo(DetailFessmanCooking::class, 'report_detail_uuid', 'uuid');
    }
}