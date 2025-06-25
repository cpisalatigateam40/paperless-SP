<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ReportPreOperation extends Model
{
    use HasFactory;

    protected $table = 'report_pre_operations';
    protected $fillable = [
        'uuid', 'area_uuid', 'product_uuid', 'production_code',
        'date', 'shift', 'created_by', 'known_by', 'approved_by', 'approved_at'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function materials()
    {
        return $this->hasMany(PreOperationMaterial::class, 'report_uuid', 'uuid');
    }

    public function packagings()
    {
        return $this->hasMany(PreOperationPackaging::class, 'report_uuid', 'uuid');
    }

    public function equipments()
    {
        return $this->hasMany(PreOperationEquipment::class, 'report_uuid', 'uuid');
    }

    public function rooms()
    {
        return $this->hasMany(PreOperationRoom::class, 'report_uuid', 'uuid');
    }
}