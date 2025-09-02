<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgingEmulsionMaking extends Model
{
    use HasFactory;

    protected $table = 'aging_emulsion_makings';

    protected $fillable = [
        'uuid',
        'header_uuid',
        'start_aging',
        'finish_aging',
        'emulsion_result',
        'sensory_color',
        'sensory_texture',
        'temp_after',
    ];

    public function header()
    {
        return $this->belongsTo(HeaderEmulsionMaking::class, 'header_uuid', 'uuid');
    }
}