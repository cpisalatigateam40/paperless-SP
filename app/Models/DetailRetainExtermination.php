<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailRetainExtermination extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'report_uuid',
        'retain_name',
        'exp_date',
        'retain_condition',
        'shape',
        'quantity',
        'quantity_kg',
        'notes',
    ];

    // relasi ke master report
    public function report()
    {
        return $this->belongsTo(ReportRetainExtermination::class, 'report_uuid', 'uuid');
    }
}