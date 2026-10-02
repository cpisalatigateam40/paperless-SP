<?php
// app/Models/ReportBoilingTank.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportBoilingTank extends Model
{
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }

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
        'is_audit',
        'source_uuid',
    ];

    protected $casts = [
        'date' => 'date',
        'known_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_audit' => 'boolean',
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

    protected function auditBooleanResetFields(): array
    {
        return [];
    }

    protected function auditNullableResetFields(): array
    {
        return ['known_by', 'known_at', 'approved_by', 'approved_at'];
    }

    public function copyToAudit(): self
    {
        return DB::transaction(function () {
            $clone = $this->copyHeaderToAudit();

            $this->copyChildren($this, $clone, [
                'details' => [
                    'checks' => [],
                ],
            ]);

            return $clone;
        });
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