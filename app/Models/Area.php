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
}
