<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DetailMaurerCooking extends Model
{
    use HasFactory;

    protected $table = 'detail_maurer_cookings';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'production_code',
        'packaging_weight',
        'trolley_count',
        'can_be_twisted'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportMaurerCooking::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function processSteps()
    {
        return $this->hasMany(ShProcessStep::class, 'report_detail_uuid', 'uuid');
    }

    public function thermocouplePositions()
    {
        return $this->hasMany(ShThermocouplePosition::class, 'report_detail_uuid', 'uuid');
    }

    public function sensoryCheck()
    {
        return $this->hasOne(ShSensoryCheck::class, 'report_detail_uuid', 'uuid');
    }

    public function showeringCoolingDown()
    {
        return $this->hasOne(ShoweringCoolingDown::class, 'report_detail_uuid', 'uuid');
    }

    public function cookingLosses()
    {
        return $this->hasMany(CookingLoss::class, 'report_detail_uuid', 'uuid');
    }

    public function totalProcessTime()
    {
        return $this->hasOne(ShTotalProcessTime::class, 'report_detail_uuid', 'uuid');
    }
}