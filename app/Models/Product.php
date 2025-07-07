<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'uuid',
        'product_name',
        'brand',
        'nett_weight',
        'shelf_life',
        'area_uuid',
    ];

    protected $casts = [
        'nett_weight' => 'float',
        'shelf_life' => 'integer',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function detailForeignObjects()
    {
        return $this->hasMany(DetailForeignObject::class, 'product_uuid', 'uuid');
    }

    public function reportProductChanges()
    {
        return $this->hasMany(ReportProductChange::class, 'product_uuid', 'uuid');
    }

    public function preOperationReports()
    {
        return $this->hasMany(ReportPreOperation::class, 'product_uuid', 'uuid');
    }

    public function detailRepackVerifs()
    {
        return $this->hasMany(DetailRepackVerif::class, 'product_uuid', 'uuid');
    }

    public function detailLabSamples()
    {
        return $this->hasMany(DetailLabSample::class, 'product_uuid', 'uuid');
    }
}