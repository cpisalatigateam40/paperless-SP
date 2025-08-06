<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FessmanProcessingStep extends Model
{
    use HasFactory;

    protected $table = 'fessman_processing_steps';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'process_name',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function standards()
    {
        return $this->hasMany(FessmanStandard::class, 'process_step_uuid', 'uuid');
    }
}