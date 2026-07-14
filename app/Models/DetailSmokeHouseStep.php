<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailSmokeHouseStep extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_smoke_house_steps';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'sequence',
        'process_name',
        'setting_temp',
        'setting_time',
        'setting_rh',
        'setting_ct',
        'actual_temp',
        'actual_time',
        'actual_rh',
        'actual_ct',
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

    public function detail()
    {
        return $this->belongsTo(
            DetailSmokeHouse::class,
            'detail_uuid',
            'uuid'
        );
    }
}