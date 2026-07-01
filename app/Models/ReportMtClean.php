<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;

class ReportMtClean extends Model
{
    use HasFactory;

    protected $table = 'report_mt_cleans';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::addGlobalScope(new UserAreaScope);
    }

    public function area()
    {
        return $this->belongsTo(
            Area::class,
            'area_uuid',
            'uuid'
        );
    }

    public function details()
    {
        return $this->hasMany(
            DetailMtClean::class,
            'report_uuid',
            'uuid'
        );
    }
}