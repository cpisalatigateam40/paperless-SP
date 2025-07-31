<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;

class QcEquipment extends Model
{
    use HasFactory;
    protected $table = 'qc_equipments';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'item_name',
        'section_name',
        'quantity',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function detailReports()
    {
        return $this->hasMany(DetailQcEquipment::class, 'qc_equipment_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}