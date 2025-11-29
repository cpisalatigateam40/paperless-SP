<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DetailPackagingVerif extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'detail_packaging_verifs';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'time',
        'production_code',
        'expired_date',
        'qc_verif',
        'kr_verif',
        'upload_md',
        'upload_qr',
        'upload_ed',
        'upload_md_multi'
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'upload_md_multi' => 'array',
    ];

    public function report()
    {
        return $this->belongsTo(ReportPackagingVerif::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function checklist()
    {
        return $this->hasOne(ChecklistPackagingDetail::class, 'detail_uuid', 'uuid');
    }
}