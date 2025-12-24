<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use Illuminate\Support\Str;

class Formula extends Model
{
    use HasFactory;

    protected $table = 'formulas';

    protected $fillable = [
        'area_uuid',
        'product_uuid',
        'product_name',
        'formula_name',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function formulations()
    {
        return $this->hasMany(Formulation::class, 'formula_uuid', 'uuid');
    }

    public function detailReports()
    {
        return $this->hasMany(DetailProcessProd::class, 'formula_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}