<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class MasterSmokeHouseStep extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'uuid',
        'master_uuid',
        'sequence',
        'process_name',
        'temperature_min',
        'temperature_max',
        'time_minutes',
        'rh',
        'core_temperature',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function master()
    {
        return $this->belongsTo(
            MasterSmokeHouse::class,
            'master_uuid',
            'uuid'
        );
    }
}