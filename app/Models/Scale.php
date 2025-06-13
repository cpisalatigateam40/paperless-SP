<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Scale extends Model
{
    use HasFactory;

    protected $table = 'scales';

    protected $fillable = ['uuid', 'area_uuid', 'code', 'type', 'brand'];

    protected static function booted()
    {
        static::creating(function ($scale) {
            $scale->uuid = (string) Str::uuid();
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }
}