<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;

class Thermometer extends Model
{
    use HasFactory;

    protected $table = 'thermometers';

    protected $fillable = [
        'area_uuid',
        'code',
        'type',
        'brand',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });

        static::addGlobalScope(new UserAreaScope);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }
}