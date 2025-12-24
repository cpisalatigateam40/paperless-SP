<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Formulation extends Model
{
    use HasFactory;

    protected $table = 'formulations';

    protected $fillable = [
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

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}