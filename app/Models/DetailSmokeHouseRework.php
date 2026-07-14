<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailSmokeHouseRework extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_smoke_house_reworks';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'smoke_house_no',
        'trolley_count',
        'stick_count',
        'start_process',
        'end_process',
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

    protected $casts = [
        'start_process' => 'datetime',
        'end_process' => 'datetime',
    ];

    public function detail()
    {
        return $this->belongsTo(
            DetailSmokeHouse::class,
            'detail_uuid',
            'uuid'
        );
    }

    public function steps()
    {
        return $this->hasMany(
            DetailSmokeHouseReworkStep::class,
            'rework_uuid',
            'uuid'
        )->orderBy('sequence');
    }
}