<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ProcessEmulsifying extends Model
{
    use HasFactory;

    protected $table = 'process_emulsifyings';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'standard_mixture_temp',
        'actual_mixture_temp_1',
        'actual_mixture_temp_2',
        'actual_mixture_temp_3',
        'average_mixture_temp'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function detail()
    {
        return $this->belongsTo(DetailProcessProd::class, 'detail_uuid', 'uuid');
    }
}