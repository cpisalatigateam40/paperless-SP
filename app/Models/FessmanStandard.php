<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FessmanStandard extends Model
{
    use HasFactory;

    protected $table = 'fessman_standards';

    protected $fillable = [
        'uuid',
        'product_uuid',
        'process_step_uuid',
        'st_min',
        'st_max',
        'time_minute_min',
        'time_minute_max',
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
        return $this->belongsTo(FessmanProcessingStep::class, 'process_step_uuid', 'uuid');
    }
}