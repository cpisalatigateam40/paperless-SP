<?php
// app/Models/GmpSanitationCheck.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class GmpSanitationCheck extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'report_gmp_sanitation_checks';

    protected $fillable = [
        'uuid', 'waktu_uuid', 'section_uuid', 'item_verifikasi',
        'standar_klorin', 'kadar_klorin', 'suhu', 'tindakan_koreksi', 'keterangan',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function waktuPemeriksaan()
    {
        return $this->belongsTo(GmpWaktuPemeriksaan::class, 'waktu_uuid', 'uuid');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }
}