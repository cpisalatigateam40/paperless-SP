<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;

class ReportMdProduct extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'report_md_products';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'date' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(DetailMdProduct::class, 'report_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}