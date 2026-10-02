<?php
// app/Models/GmpHeader.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use App\Scopes\UserAreaScope;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class GmpHeader extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }

    protected $table = 'report_gmp_headers';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'section',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'is_audit', 'source_uuid'
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'is_audit' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new UserAreaScope);
    }

    protected function auditBooleanResetFields(): array
    {
        return [];
    }

    protected function auditNullableResetFields(): array
    {
        return ['known_by', 'approved_by', 'approved_at'];
    }

    public function copyToAudit(): self
    {
        return DB::transaction(function () {
            $clone = $this->copyHeaderToAudit();

            $this->copyChildren($this, $clone, [
                'waktuPemeriksaans' => [
                    'employeeChecks'   => [],
                    'sanitationChecks' => [],
                ],
            ]);

            return $clone;
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function waktuPemeriksaans()
    {
        return $this->hasMany(GmpWaktuPemeriksaan::class, 'header_uuid', 'uuid');
    }

    public function scopeGmpKaryawan($query)
    {
        return $query->where('section', 'gmp_karyawan');
    }

    public function scopeSanitasiArea($query)
    {
        return $query->where('section', 'sanitasi_area');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }
}