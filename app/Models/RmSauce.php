<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RmSauce extends Model
{
    use HasFactory;

    protected $table = 'rm_sauces';
    protected $fillable = [
        'uuid',
        'detail_uuid',
        'raw_material_uuid',
        'amount',
        'sensory',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailSauce::class, 'detail_uuid', 'uuid');
    }


    // Relasi ke Raw Material Master
    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_uuid', 'uuid');
    }
}