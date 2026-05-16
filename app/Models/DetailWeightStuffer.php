<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class DetailWeightStuffer extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'production_code',
        'time',
        'weight_standard',
        'long_standard',
        'machine',
        'gramase'
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function report()
    {
        return $this->belongsTo(ReportWeightStuffer::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function townsend()
    {
        return $this->hasOne(TownsendStuffer::class, 'detail_uuid', 'uuid');
    }

    public function hitech()
    {
        return $this->hasOne(HitechStuffer::class, 'detail_uuid', 'uuid');
    }

    public function cases()
    {
        return $this->hasMany(CaseStuffer::class, 'stuffer_id');
    }

    public function weights()
    {
         return $this->hasMany(WeightStufferMeasurement::class, 'stuffer_id');
    }

    public function vemag()
    {
        return $this->hasOne(VemagStuffer::class, 'detail_uuid', 'uuid');
    }

    public function vemag2()
    {
        return $this->hasOne(Vemag2Stuffer::class, 'detail_uuid', 'uuid');
    }

    public function handtmann()
    {
        return $this->hasOne(HandtmannStuffer::class, 'detail_uuid', 'uuid');
    }
}