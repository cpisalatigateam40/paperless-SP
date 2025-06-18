<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'uuid',
        'product_name',
        'brand',
        'nett_weight',
        'shelf_life',
        'area_uuid',
    ];

    protected $casts = [
        'nett_weight' => 'float',
        'shelf_life' => 'integer',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }
}