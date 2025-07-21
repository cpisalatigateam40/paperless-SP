<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TofuProductInfo extends Model
{
    use HasFactory;

    protected $table = 'tofu_product_infos';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'production_code',
        'expired_date',
        'sample_amount'
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