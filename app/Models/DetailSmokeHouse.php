<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailSmokeHouse extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_smoke_houses';

    protected $fillable = [
        'uuid',
        'report_uuid',
        'master_uuid',
        'product_uuid',
        'machine_name',
        'production_code',
        'gramase',
        'smoke_house_no',
        'trolley_count',
        'stick_count',
        'start_process',
        'end_process',
        'cooling_finish',
        'documentation',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'start_process' => 'datetime',
        'end_process' => 'datetime',
        'cooling_finish' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(
            ReportSmokeHouse::class,
            'report_uuid',
            'uuid'
        );
    }

    public function master()
    {
        return $this->belongsTo(
            MasterSmokeHouse::class,
            'master_uuid',
            'uuid'
        );
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_uuid',
            'uuid'
        );
    }

    public function steps()
    {
        return $this->hasMany(
            DetailSmokeHouseStep::class,
            'detail_uuid',
            'uuid'
        )->orderBy('sequence');
    }

    public function reworks()
    {
        return $this->hasMany(
            DetailSmokeHouseRework::class,
            'detail_uuid',
            'uuid'
        );
    }

    // public function sensories()
    // {
    //     return $this->hasMany(
    //         DetailSmokeHouseSensory::class,
    //         'detail_uuid',
    //         'uuid'
    //     );
    // }
    public function sensories()
    {
        return $this->hasOne(DetailSmokeHouseSensory::class, 'detail_uuid', 'uuid');
    }
}