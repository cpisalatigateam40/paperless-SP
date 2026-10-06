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

    protected function auditNormalizeRules(): array
    {
        // GMP Karyawan: nilai boolean, 1 = OK, 0 = Tidak OK
        $employeeFields = [
            'seragam_apd_lengkap', 'sarung_tangan_utuh', 'sepatu_boots_bersih',
            'tidak_pakai_perhiasan', 'kuku_tangan_bersih', 'kuku_tidak_panjang',
            'perilaku_kerja', 'potensi_cross_contamination',
        ];

        $employeeRules = [];
        foreach ($employeeFields as $field) {
            $employeeRules[$field] = ['bad' => [0, '0', false], 'ok' => 1];
        }

        // Standar dari nama item dulu (foot basin 200, hand basin 50), baru kolom standar_klorin
        $standard = function ($row) {
            $item = strtolower($row->item_verifikasi ?? '');

            if (str_contains($item, 'foot')) return 200.0;
            if (str_contains($item, 'hand')) return 50.0;

            if ($row->standar_klorin !== null && $row->standar_klorin !== '') {
                return (float) $row->standar_klorin;
            }

            return null;
        };

        // Tidak sesuai = kadar terisi dan berbeda dari standar (di atas maupun di bawah)
        $deviates = function ($row) use ($standard) {
            $std = $standard($row);

            return $std !== null
                && $row->kadar_klorin !== null
                && $row->kadar_klorin !== ''
                && abs((float) $row->kadar_klorin - $std) > 0.0001;
        };

        return [
            'waktuPemeriksaans.employeeChecks' => $employeeRules,
            'waktuPemeriksaans.sanitationChecks' => [
                'kadar_klorin'  => ['when' => $deviates, 'ok' => $standard],
                // kolom standar ikut dibetulkan kalau salah tersimpan (mis. 300)
                'standar_klorin' => ['when' => $deviates, 'ok' => $standard],
            ],
        ];
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