<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;

class Premix extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_uuid',
        'name',
        'producer',
        'production_code',
        'shelf_life',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
        static::addGlobalScope(new UserAreaScope);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function detailPremixes()
    {
        return $this->hasMany(DetailPremix::class, 'premix_uuid', 'uuid');
    }

    public function detailEmulsionMakings()
    {
        return $this->hasMany(DetailEmulsionMaking::class, 'material_uuid', 'uuid')
                    ->where('material_type', 'premix');
    }
}