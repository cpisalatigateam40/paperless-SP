<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class DetailEmulsionMaking extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_emulsion_makings';

    protected $fillable = [
        'uuid',
        'header_uuid',
        'raw_material_uuid',
        'weight',
        'temperature',
        'sensory',
        'conformity',
        'aging_index'
    ];

    protected $auditEvents = [
        'updated',
    ];

    public function header()
    {
        return $this->belongsTo(HeaderEmulsionMaking::class, 'header_uuid', 'uuid');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_uuid', 'uuid');
    }
}