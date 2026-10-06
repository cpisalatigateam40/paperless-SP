<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportPackagingVerif extends Model implements Auditable
{
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'report_packaging_verifs';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'section_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'is_audit',
        'source_uuid',
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = [
        'is_audit' => 'boolean',
    ];

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
                'details' => [
                    'checklist' => [],
                ],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        $filled = fn ($v) => $v !== null && $v !== '';

        // "12-13" / "12 - 13" / "12,5-13" => [min, max]; angka tunggal => [n, n]
        $parseRange = function ($std) {
            if (!$std) {
                return null;
            }
            preg_match_all('/\d+(?:[.,]\d+)?/', (string) $std, $m);
            $nums = array_map(fn ($n) => (float) str_replace(',', '.', $n), $m[0]);

            if (count($nums) === 0) {
                return null;
            }
            if (count($nums) === 1) {
                return [$nums[0], $nums[0]];
            }

            return [min($nums[0], $nums[1]), max($nums[0], $nums[1])];
        };

        // Aturan untuk satu kelompok ukuran: standar, aktual 1..5, rata-rata
        $measureRules = function (string $stdField, string $actualPrefix, string $avgField) use ($filled, $parseRange) {
            $keys = array_map(fn ($i) => "{$actualPrefix}_{$i}", range(1, 5));

            $outOfRange = function ($row, $key) use ($stdField, $filled, $parseRange) {
                $range = $parseRange($row->{$stdField});
                if (!$range || !$filled($row->{$key})) {
                    return false;
                }
                $v = (float) $row->{$key};

                return $v < $range[0] - 0.0001 || $v > $range[1] + 0.0001;
            };

            // nilai pengganti: titik tengah standar
            $target = function ($row) use ($stdField, $parseRange) {
                $range = $parseRange($row->{$stdField});

                return round(($range[0] + $range[1]) / 2, 2);
            };

            $rules = [];

            foreach ($keys as $key) {
                $rules[$key] = [
                    'when' => fn ($row) => $outOfRange($row, $key),
                    'ok'   => $target,
                ];
            }

            // rata-rata dihitung ulang dari aktual yang terisi (setelah koreksi)
            $rules[$avgField] = [
                'when' => function ($row) use ($keys, $outOfRange) {
                    foreach ($keys as $key) {
                        if ($outOfRange($row, $key)) {
                            return true;
                        }
                    }
                    return false;
                },
                'ok' => function ($row) use ($keys, $filled, $outOfRange, $target) {
                    $values = [];
                    foreach ($keys as $key) {
                        if ($filled($row->{$key})) {
                            $values[] = $outOfRange($row, $key) ? $target($row) : (float) $row->{$key};
                        }
                    }

                    return round(array_sum($values) / count($values), 2);
                },
            ];

            return $rules;
        };

        $okRule = ['bad' => ['Tidak OK'], 'ok' => 'OK'];

        $checklist = ['sampling_result' => $okRule];

        for ($i = 1; $i <= 5; $i++) {
            $checklist["sealing_condition_{$i}"] = $okRule;
            $checklist["sealing_vacuum_{$i}"]    = $okRule;
        }

        $checklist += $measureRules('standard_long_pcs',   'actual_long_pcs',   'avg_long_pcs');
        $checklist += $measureRules('standard_weight_pcs', 'actual_weight_pcs', 'avg_weight_pcs');
        $checklist += $measureRules('standard_weight',     'actual_weight',     'avg_weight');

        return [
            'details.checklist' => $checklist,
        ];
    }

    public function details()
    {
        return $this->hasMany(DetailPackagingVerif::class, 'report_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }
}