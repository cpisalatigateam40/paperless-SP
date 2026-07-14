<?php

namespace App\Models;

use App\Scopes\UserAreaScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class MasterSmokeHouse extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'uuid',
        'area_uuid',
        'product_uuid',
        'machine_name',
        'remarks',
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

        static::addGlobalScope(new UserAreaScope);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function steps()
    {
        return $this->hasMany(
            MasterSmokeHouseStep::class,
            'master_uuid',
            'uuid'
        )->orderBy('sequence');
    }

    public function reports()
    {
        return $this->hasMany(
            DetailSmokeHouse::class,
            'master_uuid',
            'uuid'
        );
    }
}