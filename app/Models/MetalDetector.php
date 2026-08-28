<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;

class MetalDetector extends Model
{

    protected $table = 'metal_detectors';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'merk',
        'type_model',
        'no_series',
        'is_active',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}