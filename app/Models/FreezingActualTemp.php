<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class FreezingActualTemp extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'freezing_uuid',
        'actual_temp',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function freezing()
    {
        return $this->belongsTo(DataFreezing::class, 'freezing_uuid', 'uuid');
    }
}