<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeightStufferMeasurement extends Model
{
    protected $fillable = ['stuffer_id', 'actual_weight', 'actual_long','actual_fla'];

    public function detail()
    {
        return $this->belongsTo(DetailWeightStuffer::class, 'stuffer_id');
    }
}