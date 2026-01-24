<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class DetailLabSample extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'production_code',
        'best_before',
        'quantity',
        'notes',
        'gramase',
        'sample_type',
        'unit',
    ];

    protected $auditEvents = [
        'updated',
    ];

    // Relasi ke report
    public function report()
    {
        return $this->belongsTo(ReportLabSample::class, 'report_uuid', 'uuid');
    }

    // Relasi ke product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}