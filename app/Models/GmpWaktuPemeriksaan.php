<?php
// app/Models/GmpWaktuPemeriksaan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class GmpWaktuPemeriksaan extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'report_gmp_waktu_pemeriksaans';

    protected $fillable = [
        'uuid',
        'header_uuid',
        'waktu_ke',
        'jam_pemeriksaan',
        'catatan',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function header()
    {
        return $this->belongsTo(GmpHeader::class, 'header_uuid', 'uuid');
    }

    public function employeeChecks()
    {
        return $this->hasMany(GmpEmployeeCheck::class, 'waktu_uuid', 'uuid');
    }

    public function sanitationChecks()
    {
        return $this->hasMany(GmpSanitationCheck::class, 'waktu_uuid', 'uuid');
    }
}