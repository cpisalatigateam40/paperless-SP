<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportProdLossVacum extends Model
{
    use HasFactory;

    protected $table = 'report_prod_loss_vacums';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailProdLossVacum::class, 'report_uuid', 'uuid');
    }
}