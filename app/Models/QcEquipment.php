<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}