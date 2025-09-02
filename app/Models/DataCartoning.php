<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DataCartoning extends Model
{
    use HasFactory;

    protected $table = 'data_cartonings';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'carton_code',
        'content_bag',
        'content_binded',
        'carton_weight_standard',
        'carton_weight_actual',
        'weight_1',
        'weight_2',
        'weight_3',
        'weight_4',
        'weight_5',
        'avg_weight',
        'content_rtg',
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