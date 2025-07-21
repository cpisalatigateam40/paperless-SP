<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TofuWeightVerif extends Model
{
    use HasFactory;

    protected $table = 'tofu_weight_verifs';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'weight_category',
        'turus',
        'total',
        'percentage'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportTofuVerif::class, 'report_uuid', 'uuid');
    }
}