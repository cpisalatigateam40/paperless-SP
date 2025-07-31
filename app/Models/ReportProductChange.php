<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;

class ReportProductChange extends Model
{
    use HasFactory;

    protected $table = 'report_product_changes';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'product_uuid',
        'production_code',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function materialLeftovers()
    {
        return $this->hasMany(VerificationMaterialLeftover::class, 'report_uuid', 'uuid');
    }

    public function equipments()
    {
        return $this->hasMany(VerificationEquipment::class, 'report_uuid', 'uuid');
    }

    public function sections()
    {
        return $this->hasMany(VerificationSection::class, 'report_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}