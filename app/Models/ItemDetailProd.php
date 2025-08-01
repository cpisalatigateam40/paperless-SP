<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ItemDetailProd extends Model
{
    use HasFactory;

    protected $table = 'item_detail_prods';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'formulation_uuid',
        'actual_weight',
        'sensory',
        'temperature'
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

    public function formulation()
    {
        return $this->belongsTo(Formulation::class, 'formulation_uuid', 'uuid');
    }
}