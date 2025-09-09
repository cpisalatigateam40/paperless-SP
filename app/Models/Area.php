<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    protected $fillable = ['uuid', 'name'];

    protected static function booted()
    {
        static::creating(function ($area) {
            if (empty($area->uuid)) {
                $area->uuid = (string) Str::uuid();
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class, 'area_uuid', 'uuid');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'area_uuid', 'uuid');
    }

    public function rawMaterials()
    {
        return $this->hasMany(RawMaterial::class, 'area_uuid', 'uuid');
    }

    public function reportStorageRM()
    {
        return $this->hasMany(ReportStorageRmCleanliness::class, 'area_uuid', 'uuid');
    }

    public function reportProcessArea()
    {
        return $this->hasMany(ReportProcessAreaCleanliness::class, 'area_uuid', 'uuid');
    }

    public function fragileItems()
    {
        return $this->hasMany(FragileItem::class, 'area_uuid', 'uuid');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'area_uuid', 'uuid');
    }

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class, 'area_uuid', 'uuid');
    }

    public function repairReports()
    {
        return $this->hasMany(ReportRepairCleanliness::class, 'area_uuid', 'uuid');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'area_uuid', 'uuid');
    }

    public function reports()
    {
        return $this->hasMany(ReportConveyorCleanliness::class, 'area_uuid', 'uuid');
    }

    public function reportSolvents()
    {
        return $this->hasMany(ReportSolvent::class, 'area_uuid', 'uuid');
    }

    public function reportRmArrivals()
    {
        return $this->hasMany(ReportRmArrival::class, 'area_uuid', 'uuid');
    }

    public function premixes()
    {
        return $this->hasMany(Premix::class, 'area_uuid', 'uuid');
    }

    public function reportPremixes()
    {
        return $this->hasMany(ReportPremix::class, 'area_uuid', 'uuid');
    }

    public function foreignObjectReports()
    {
        return $this->hasMany(ReportForeignObject::class, 'area_uuid', 'uuid');
    }

    public function reportMagnetTraps()
    {
        return $this->hasMany(ReportMagnetTrap::class, 'area_uuid', 'uuid');
    }

    public function sharpTools()
    {
        return $this->hasMany(SharpTool::class, 'area_uuid', 'uuid');
    }

    public function reportSharpTools()
    {
        return $this->hasMany(ReportSharpTool::class, 'area_uuid', 'uuid');
    }

    public function reportProductChanges()
    {
        return $this->hasMany(ReportProductChange::class, 'area_uuid', 'uuid');
    }

    public function preOperationReports()
    {
        return $this->hasMany(ReportPreOperation::class, 'area_uuid', 'uuid');
    }

    public function productionNonconformities()
    {
        return $this->hasMany(ReportProductionNonconformity::class, 'area_uuid', 'uuid');
    }

    public function chlorineResidueReports()
    {
        return $this->hasMany(ReportChlorineResidue::class, 'area_uuid', 'uuid');
    }

    public function reportRepackVerifs()
    {
        return $this->hasMany(ReportRepackVerif::class, 'area_uuid', 'uuid');
    }

    public function reportLabSamples()
    {
        return $this->hasMany(ReportLabSample::class, 'area_uuid', 'uuid');
    }

    public function reportReturns()
    {
        return $this->hasMany(ReportReturn::class, 'area_uuid', 'uuid');
    }

    public function reportMetals()
    {
        return $this->hasMany(ReportMetalDetector::class, 'area_uuid', 'uuid');
    }

    public function reportRetains()
    {
        return $this->hasMany(ReportRetain::class, 'area_uuid', 'uuid');
    }

    public function iqfFreezingReports()
    {
        return $this->hasMany(ReportIqfFreezing::class, 'area_uuid', 'uuid');
    }

    public function reportVacuumConditions()
    {
        return $this->hasMany(ReportVacuumCondition::class, 'area_uuid', 'uuid');
    }

    public function reportMdProducts()
    {
        return $this->hasMany(ReportMdProduct::class, 'area_uuid', 'uuid');
    }

    public function retainExterminations()
    {
        return $this->hasMany(ReportRetainExtermination::class, 'area_uuid', 'uuid');
    }

    public function reportStuffers()
    {
        return $this->hasMany(ReportStuffer::class, 'area_uuid', 'uuid');
    }

    public function reportFreezPackagings()
    {
        return $this->hasMany(ReportFreezPackaging::class, 'area_uuid', 'uuid');
    }

    public function checkweigherReports()
    {
        return $this->hasMany(ReportCheckweigherBox::class, 'area_uuid', 'uuid');
    }

    public function reportRetainSamples()
    {
        return $this->hasMany(ReportRetainSample::class, 'area_uuid', 'uuid');
    }

    public function reportTofuVerifs()
    {
        return $this->hasMany(ReportTofuVerif::class, 'area_uuid', 'uuid');
    }

    public function reportLossVacums()
    {
        return $this->hasMany(ReportProdLossVacum::class, 'area_uuid', 'uuid');
    }

    public function reportPackagingVerifs()
    {
        return $this->hasMany(ReportPackagingVerif::class, 'area_uuid', 'uuid');
    }

    public function formulas()
    {
        return $this->hasMany(Formula::class, 'area_uuid', 'uuid');
    }

    public function reportProcessProds()
    {
        return $this->hasMany(ReportProcessProd::class, 'area_uuid', 'uuid');
    }

    public function standardStuffers()
    {
        return $this->hasMany(StandardStuffer::class, 'area_uuid', 'uuid');
    }

    public function basoCookings()
    {
        return $this->hasMany(ReportBasoCooking::class, 'area_uuid', 'uuid');
    }

    public function reportRtgSteamers()
    {
        return $this->hasMany(ReportRtgSteamer::class, 'area_uuid', 'uuid');
    }



}