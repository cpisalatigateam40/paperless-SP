<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailFessmanCooking extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_fessman_cookings';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'production_code',
        'packaging_weight',
        'trolley_count',
        'start_time',
        'end_time',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportFessmanCooking::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function processSteps()
    {
        return $this->hasMany(FsProcessStep::class, 'report_detail_uuid', 'uuid');
    }

    public function coolingDowns()
    {
        return $this->hasMany(FsCoolingDown::class, 'report_detail_uuid', 'uuid');
    }

    public function sensoryCheck()
    {
        return $this->hasOne(FsSensoryCheck::class, 'report_detail_uuid', 'uuid');
    }
}