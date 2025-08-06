<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaurerStandard extends Model
{
    use HasFactory;

    protected $table = 'maurer_standards';

    protected $fillable = [
        'uuid',
        'product_uuid',
        'process_step_uuid',
        'st_min',
        'st_max',
        'time_minute',
        'rh_min',
        'rh_max',
        'ct_min',
        'ct_max',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function processStep()
    {
        return $this->belongsTo(MaurerProcessingStep::class, 'process_step_uuid', 'uuid');
    }
}