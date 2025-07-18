<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DetailProductVerif extends Model
{
    use HasFactory;

    protected $table = 'detail_product_verifs';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'jam',
        'production_code',
        'expired_date',
        'long_standard',
        'weight_standard',
        'diameter_standard',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportProductVerif::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function measurements()
    {
        return $this->hasMany(ProductVerifMeasurement::class, 'detail_uuid', 'uuid');
    }
}