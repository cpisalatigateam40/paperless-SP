<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailForeignObject extends Model
{
    use HasFactory;

    protected $table = 'detail_foreign_objects';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'time',
        'production_code',
        'contaminant_type',
        'evidence',
        'analysis_stage',
        'contaminant_origin',
    ];

    public function report()
    {
        return $this->belongsTo(ReportForeignObject::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}