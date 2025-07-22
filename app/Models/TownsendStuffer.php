<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TownsendStuffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_uuid',
        'stuffer_speed',
        'trolley_total',
        'avg_weight',
        'notes'
    ];

    public function detail()
    {
        return $this->belongsTo(DetailWeightStuffer::class, 'detail_uuid', 'uuid');
    }
}