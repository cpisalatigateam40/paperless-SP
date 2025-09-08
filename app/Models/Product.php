<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;

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

    public function detailMetalDetectors()
    {
        return $this->hasMany(DetailMetalDetector::class, 'product_uuid', 'uuid');
    }

    public function detailRetains()
    {
        return $this->hasMany(DetailRetain::class, 'product_uuid', 'uuid');
    }

    public function iqfFreezingDetails()
    {
        return $this->hasMany(DetailIqfFreezing::class, 'product_uuid', 'uuid');
    }

    public function detailVacuumConditions()
    {
        return $this->hasMany(DetailVacuumCondition::class, 'product_uuid', 'uuid');
    }

    public function detailMdProducts()
    {
        return $this->hasMany(DetailMdProduct::class, 'product_uuid', 'uuid');
    }

    public function detailStuffers()
    {
        return $this->hasMany(DetailStuffer::class, 'product_uuid', 'uuid');
    }

    public function cookingLossStuffers()
    {
        return $this->hasMany(CookingLossStuffer::class, 'product_uuid', 'uuid');
    }

    public function detailFreezPackagings()
    {
        return $this->hasMany(DetailFreezPackaging::class, 'product_uuid', 'uuid');
    }

    public function checkweigherDetails()
    {
        return $this->hasMany(DetailCheckweigherBox::class, 'product_uuid', 'uuid');
    }

    public function retainSampleDetails()
    {
        return $this->hasMany(DetailRetainSample::class, 'product_uuid', 'uuid');
    }

    public function detailLossVacums()
    {
        return $this->hasMany(DetailProdLossVacum::class, 'product_uuid', 'uuid');
    }

    public function detailPackagingVerifs()
    {
        return $this->hasMany(DetailPackagingVerif::class, 'product_uuid', 'uuid');
    }

    public function formulas()
    {
        return $this->hasMany(Formula::class, 'product_uuid', 'uuid');
    }

    public function detailReports()
    {
        return $this->hasMany(DetailProcessProd::class, 'product_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }

    public function standardStuffers()
    {
        return $this->hasMany(StandardStuffer::class, 'product_uuid', 'uuid');
    }

    public function maurerStandardProcesses()
    {
        return $this->hasMany(MaurerStandard::class, 'product_uuid', 'uuid')->orderBy('order');
    }

    public function basoCookings()
    {
        return $this->hasMany(ReportBasoCooking::class, 'product_uuid', 'uuid');
    }




}