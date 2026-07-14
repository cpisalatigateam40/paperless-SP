<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class DetailSmokeHouseSensory extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'detail_smoke_house_sensories';

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'type',
        'appearance',
        'color',
        'aroma',
        'taste',
        'texture',
        'notes',
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