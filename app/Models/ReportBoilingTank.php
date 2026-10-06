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

    protected function auditNormalizeRules(): array
    {
        $filled = fn ($v) => $v !== null && $v !== '';

        // master standar dimuat sekali (lazy) per proses normalisasi, bukan per baris
        $master = null;
        $loaded = false;
        $getMaster = function () use (&$master, &$loaded) {
            if (!$loaded) {
                $master = \App\Models\MasterBoilingTankStandard::where('product_uuid', $this->product_uuid)
                    ->where('area_uuid', $this->area_uuid)
                    ->first();
                $loaded = true;
            }

            return $master;
        };

        // [min, max]; kalau max kosong, standar dianggap nilai tunggal (min)
        $range = function (string $prefix) use ($getMaster, $filled) {
            $m = $getMaster();
            if (!$m || !$filled($m->{$prefix . '_min'})) {
                return null;
            }

            $min = (float) $m->{$prefix . '_min'};
            $max = $filled($m->{$prefix . '_max'}) ? (float) $m->{$prefix . '_max'} : $min;

            return [min($min, $max), max($min, $max)];
        };

        // hanya nilai yang terisi DAN di luar standar
        $outOfRange = function ($row, string $field, string $prefix) use ($range, $filled) {
            $r = $range($prefix);
            if (!$r || !$filled($row->{$field})) {
                return false;
            }
            $v = (float) $row->{$field};

            return $v < $r[0] - 0.0001 || $v > $r[1] + 0.0001;
        };

        // nilai pengganti: titik tengah standar (75 - 85 => 80; nilai tunggal => nilai itu)
        $rule = fn (string $field, string $prefix) => [
            'when' => fn ($row) => $outOfRange($row, $field, $prefix),
            'ok'   => function ($row) use ($range, $prefix) {
                $r = $range($prefix);

                return round(($r[0] + $r[1]) / 2, 2);
            },
        ];

        $okRule = ['bad' => ['Tidak OK'], 'ok' => 'OK'];

        return [
            'details' => [
                'aktual_suhu_tangki_1' => $rule('aktual_suhu_tangki_1', 'suhu_tangki_1'),
                'aktual_suhu_tangki_2' => $rule('aktual_suhu_tangki_2', 'suhu_tangki_2'),
                'sensori_bentuk'  => $okRule,
                'sensori_warna'   => $okRule,
                'sensori_aroma'   => $okRule,
                'sensori_rasa'    => $okRule,
                'sensori_tekstur' => $okRule,
            ],
            'details.checks' => [
                'berat_mentah'     => $rule('berat_mentah', 'berat_mentah'),
                'actual_core_temp' => $rule('actual_core_temp', 'actual_core_temp'),
                'berat_matang'     => $rule('berat_matang', 'berat_matang'),
            ],
        ];
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