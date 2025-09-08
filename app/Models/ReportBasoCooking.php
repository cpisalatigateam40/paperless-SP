<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportBasoCooking extends Model
{
    use HasFactory;

    protected $table = 'report_baso_cookings';
    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'product_uuid',
        'std_core_temp',
        'std_weight',
        'set_boiling_1',
        'set_boiling_2',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
    ];

    // Relasi ke Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    // Relasi ke Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    // Relasi ke Detail
    public function details()
    {
        return $this->hasMany(DetailBasoCooking::class, 'report_uuid', 'uuid');
    }
}