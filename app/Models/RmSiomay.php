<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class RmSiomay extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'rm_siomays';
    protected $fillable = [
        'uuid',
        'detail_uuid',
        'raw_material_uuid',
        'amount',
        'sensory',
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailSiomay::class, 'detail_uuid', 'uuid');
    }


    // Relasi ke Raw Material Master
    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_uuid', 'uuid');
    }
}