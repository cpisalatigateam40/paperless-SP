<?php
// app/Models/GmpHeader.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class GmpHeader extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

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
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

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