<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReportMdRejectDetail extends Model
{
    protected $table = 'report_md_reject_details';

    public const CAUSES = [
        'sensitivity'  => 'Sensitivitas alat terlalu tinggi',
        'wet_product'  => 'Produk basah / suhu produk tidak sesuai',
        'belt_position'=> 'Posisi produk tidak tepat di belt',
        'disturbance'  => 'Gangguan listrik / getaran',
        'calibration'  => 'Kalibrasi belum sesuai',
        'other'        => 'Lainnya',
    ];

    public const DISPOSITIONS = [
        'release' => 'Release (Rework)',
        'hold'    => 'Hold',
        'reject'  => 'Reject',
    ];

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'time',
        'production_code',
        'reject_qty',
        'recheck_result',
        'contamination_found',
        'suspected_causes',
        'suspected_cause_other',
        'corrective_action',
        'disposition',
    ];

    protected $casts = [
        'reject_qty'          => 'integer',
        'contamination_found' => 'boolean',
        'suspected_causes'    => 'array',
    ];

    protected static function booted()
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    public function report()
    {
        return $this->belongsTo(ReportMdReject::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}