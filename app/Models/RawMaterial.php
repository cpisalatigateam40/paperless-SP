<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;

class RawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_name',
        'supplier',
        'area_uuid',
        'shelf_life',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
        static::addGlobalScope(new UserAreaScope);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function detailArrivals()
    {
        return $this->hasMany(DetailRmArrival::class, 'raw_material_uuid', 'uuid');
    }

    public function detailReturns()
    {
        return $this->hasMany(DetailReturn::class, 'rm_uuid', 'uuid');
    }

    public function formulations()
    {
        return $this->hasMany(Formulation::class, 'raw_material_uuid', 'uuid');
    }
}