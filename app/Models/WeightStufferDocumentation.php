<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeightStufferDocumentation extends Model
{
    protected $fillable = [
        'uuid',
        'detail_id',
        'image',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailWeightStuffer::class, 'detail_id');
    }
}