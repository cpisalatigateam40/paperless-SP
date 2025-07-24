<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailEmulsionMaking extends Model
{
    use HasFactory;

    protected $table = 'detail_emulsion_makings';

    protected $fillable = [
        'uuid',
        'header_uuid',
        'raw_material_uuid',
        'weight',
        'temperature',
        'sensory',
        'aging_index'
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