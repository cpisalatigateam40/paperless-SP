<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StandardStuffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'area_uuid',
        'product_uuid',
        'long_min',
        'long_max',
        'diameter',
        'weight_min',
        'weight_max',
    ];

    // Relasi ke Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    // Relasi ke Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }
}