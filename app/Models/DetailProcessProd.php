<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DetailProcessProd extends Model
{
    use HasFactory;

    protected $table = 'detail_process_prods';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'formula_uuid',
        'production_code',
        'mixing_time',
        'rework_kg',
        'rework_percent',
        'total_material'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(ReportProcessProd::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function formula()
    {
        return $this->belongsTo(Formula::class, 'formula_uuid', 'uuid');
    }

    public function items()
    {
        return $this->hasMany(ItemDetailProd::class, 'detail_uuid', 'uuid');
    }

    public function emulsifying()
    {
        return $this->hasOne(ProcessEmulsifying::class, 'detail_uuid', 'uuid');
    }

    public function sensoric()
    {
        return $this->hasOne(ProcessSensoric::class, 'detail_uuid', 'uuid');
    }

    public function tumbling()
    {
        return $this->hasOne(ProcessTumbling::class, 'detail_uuid', 'uuid');
    }

    public function aging()
    {
        return $this->hasOne(ProcessAging::class, 'detail_uuid', 'uuid');
    }
}