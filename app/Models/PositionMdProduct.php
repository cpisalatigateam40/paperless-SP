<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionMdProduct extends Model
{
    protected $table = 'position_md_products';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'specimen',
        'position',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailMdProduct::class, 'detail_uuid', 'uuid');
    }
}