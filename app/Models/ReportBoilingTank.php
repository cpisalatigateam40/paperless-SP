<?php
// app/Models/ReportBoilingTank.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;

class ReportBoilingTank extends Model
{
    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'product_uuid',
        'product_code',
        'gramasi',
        'line_boiling_tank',
        'waktu_proses_start',
        'waktu_proses_end',
        'status',
        'link_kurva',
        'created_by',
        'known_by',
        'known_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'known_at' => 'datetime',
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

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailBoilingTank::class, 'report_uuid', 'uuid');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}