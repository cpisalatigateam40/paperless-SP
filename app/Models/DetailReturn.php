<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailReturn extends Model
{
    use HasFactory;

    protected $table = 'detail_returns';

    protected $fillable = [
        'report_uuid',
        'rm_uuid',
        'supplier',
        'production_code',
        'hold_reason',
        'quantity',
        'unit',
        'action',
    ];

    public function report()
    {
        return $this->belongsTo(ReportReturn::class, 'report_uuid', 'uuid');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'rm_uuid', 'uuid');
    }
}