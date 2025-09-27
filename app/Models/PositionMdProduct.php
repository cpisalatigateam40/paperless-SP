<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PositionMdProduct extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'position_md_products';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'specimen',
        'position',
        'status',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function detail()
    {
        return $this->belongsTo(DetailMdProduct::class, 'detail_uuid', 'uuid');
    }
}