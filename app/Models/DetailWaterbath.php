<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class DetailWaterbath extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_waterbaths';
    protected $fillable = [
        'uuid', 'report_uuid', 'product_uuid',
        'batch_code', 'amount', 'unit', 'note'
    ];

    protected $auditEvents = [
        'updated',
    ];

    // Relasi ke report
    public function report()
    {
        return $this->belongsTo(ReportWaterbath::class, 'report_uuid', 'uuid');
    }

    // Relasi ke produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

}