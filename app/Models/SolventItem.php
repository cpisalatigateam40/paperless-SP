<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SolventItem extends Model
{
    use HasFactory;

    protected $table = 'solvent_items';

    protected $fillable = [
        'uuid',
        'name',
        'concentration',
        'volume_material',
        'volume_solvent',
        'application_area',
    ];

    public function details()
    {
        return $this->hasMany(DetailSolvent::class, 'solvent_uuid', 'uuid');
    }
}