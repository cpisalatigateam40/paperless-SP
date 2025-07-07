<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailRepackVerif extends Model
{
    use HasFactory;

    protected $table = 'detail_repack_verifs';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'production_code',
        'expired_date',
        'reason',
        'notes',
    ];

    // Relasi ke report
    public function report()
    {
        return $this->belongsTo(ReportRepackVerif::class, 'report_uuid', 'uuid');
    }

    // Relasi ke product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}