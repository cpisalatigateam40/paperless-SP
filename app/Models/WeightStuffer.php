<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeightStuffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'stuffer_id',
        'actual_weight_1',
        'actual_weight_2',
        'actual_weight_3',
        'actual_long_1',
        'actual_long_2',
        'actual_long_3',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailWeightStuffer::class, 'stuffer_id');
    }
}