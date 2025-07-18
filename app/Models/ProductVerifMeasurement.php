<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ProductVerifMeasurement extends Model
{
    use HasFactory;

    protected $table = 'product_verif_measurements';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'sequence',
        'length_actual',
        'weight_actual',
        'diameter_actual',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function detail()
    {
        return $this->belongsTo(DetailProductVerif::class, 'detail_uuid', 'uuid');
    }
}