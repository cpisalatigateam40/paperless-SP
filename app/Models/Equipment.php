<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;

class Equipment extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'equipments';

    protected $fillable = ['uuid', 'name', 'area_uuid'];

    protected $auditEvents = [
        'updated',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });

        static::addGlobalScope(new UserAreaScope);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(EquipmentPart::class, 'equipment_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function cleanlinessDetails()
    {
        return $this->hasMany(DetailRepairCleanliness::class, 'equipment_uuid', 'uuid');
    }

    public function verificationEquipments()
    {
        return $this->hasMany(VerificationEquipment::class, 'equipment_uuid', 'uuid');
    }

    public function preOperationEquipments()
    {
        return $this->hasMany(PreOperationEquipment::class, 'equipment_uuid', 'uuid');
    }
}