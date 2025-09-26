<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailLabSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'production_code',
        'best_before',
        'quantity',
        'notes',
        'gramase',
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