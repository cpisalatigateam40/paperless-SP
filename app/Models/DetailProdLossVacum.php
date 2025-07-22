<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailProdLossVacum extends Model
{
    use HasFactory;

    protected $table = 'detail_prod_loss_vacums';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'product_uuid',
        'production_code',
        'vacum_machine',
        'sample_amount'
    ];

    public function report()
    {
        return $this->belongsTo(ReportProdLossVacum::class, 'report_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function defects()
    {
        return $this->hasMany(LossVacumDefect::class, 'detail_uuid', 'uuid');
    }
}