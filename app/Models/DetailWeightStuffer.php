<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailWeightStuffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'production_code',
        'time',
        'weight_standard',
        'long_standard'
    ];

    public function report()
    {
        return $this->belongsTo(ReportWeightStuffer::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function townsend()
    {
        return $this->hasOne(TownsendStuffer::class, 'detail_uuid', 'uuid');
    }

    public function hitech()
    {
        return $this->hasOne(HitechStuffer::class, 'detail_uuid', 'uuid');
    }

    public function cases()
    {
        return $this->hasMany(CaseStuffer::class, 'stuffer_id');
    }

    public function weights()
    {
        return $this->hasMany(WeightStuffer::class, 'stuffer_id');
    }
}