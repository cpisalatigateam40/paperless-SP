<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';

    protected $fillable = [
        'uuid',
        'section_name',
        'area_uuid'
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto generate UUID saat membuat data baru
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }
}