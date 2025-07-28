<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Formulation extends Model
{
    use HasFactory;

    protected $table = 'formulations';

    protected $fillable = [
        'uuid',
        'raw_material_uuid',
        'premix_uuid',
        'formula_uuid',
        'formulation_name',
        'weight',
    ];

    public function formula()
    {
        return $this->belongsTo(Formula::class, 'formula_uuid', 'uuid');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_uuid', 'uuid');
    }

    public function premix()
    {
        return $this->belongsTo(Premix::class, 'premix_uuid', 'uuid');
    }

    public function itemDetails()
    {
        return $this->hasMany(ItemDetailProd::class, 'formulation_uuid', 'uuid');
    }
}