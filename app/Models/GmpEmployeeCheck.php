<?php
// app/Models/GmpEmployeeCheck.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class GmpEmployeeCheck extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'report_gmp_employee_checks';

    protected $fillable = [
        'uuid', 'waktu_uuid', 'section_uuid', 'employee_name',
        'seragam_apd_lengkap', 'sarung_tangan_utuh', 'sepatu_boots_bersih',
        'tidak_pakai_perhiasan', 'kuku_tangan_bersih', 'kuku_tidak_panjang',
        'perilaku_kerja', 'potensi_cross_contamination', 'tindakan_koreksi', 'keterangan'
    ];

    protected $casts = [
        'seragam_apd_lengkap' => 'boolean',
        'sarung_tangan_utuh' => 'boolean',
        'sepatu_boots_bersih' => 'boolean',
        'tidak_pakai_perhiasan' => 'boolean',
        'kuku_tangan_bersih' => 'boolean',
        'kuku_tidak_panjang' => 'boolean',
        'perilaku_kerja' => 'boolean',
        'potensi_cross_contamination' => 'boolean',
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