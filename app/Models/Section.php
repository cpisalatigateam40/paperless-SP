<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';

    protected $fillable = [
        'uuid',
        'section_name',
        'area_uuid'
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto generate UUID saat membuat data baru
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function cleanlinessDetails()
    {
        return $this->hasMany(DetailRepairCleanliness::class, 'section_uuid', 'uuid');
    }

    public function reports()
    {
        return $this->hasMany(ReportConveyorCleanliness::class, 'section_uuid', 'uuid');
    }

    public function foreignObjectReports()
    {
        return $this->hasMany(ReportForeignObject::class, 'section_uuid', 'uuid');
    }

    public function reportMagnetTraps()
    {
        return $this->hasMany(ReportMagnetTrap::class, 'section_uuid', 'uuid');
    }

    public function verificationSections()
    {
        return $this->hasMany(VerificationSection::class, 'section_uuid', 'uuid');
    }

    public function preOperationRooms()
    {
        return $this->hasMany(PreOperationRoom::class, 'section_uuid', 'uuid');
    }

    public function chlorineResidueReports()
    {
        return $this->hasMany(ReportChlorineResidue::class, 'section_uuid', 'uuid');
    }

    public function reportMetals()
    {
        return $this->hasMany(ReportMetalDetector::class, 'section_uuid', 'uuid');
    }

    public function reportRetains()
    {
        return $this->hasMany(ReportRetain::class, 'section_uuid', 'uuid');
    }

    public function reportPackagingVerifs()
    {
        return $this->hasMany(ReportPackagingVerif::class, 'section_uuid', 'uuid');
    }

    public function reportProcessProds()
    {
        return $this->hasMany(ReportProcessProd::class, 'section_uuid', 'uuid');
    }
}