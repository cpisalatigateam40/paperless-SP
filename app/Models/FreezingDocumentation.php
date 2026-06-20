<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FreezingDocumentation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'detail_uuid',
        'image',
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
            DetailFreezPackaging::class,
            'detail_uuid',
            'uuid'
        );
    }
}