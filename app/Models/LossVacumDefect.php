<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LossVacumDefect extends Model
{
    use HasFactory;

    protected $table = 'loss_vacum_defects';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'category',
        'pack_amount',
        'percentage'
    ];

    public function detail()
    {
        return $this->belongsTo(DetailProdLossVacum::class, 'detail_uuid', 'uuid');
    }
}