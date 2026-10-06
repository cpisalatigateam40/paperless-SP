<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportProcessProd extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_process_prods';

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
        'notes',
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
                'detail' => [
                    'items'       => [],
                    'emulsifying' => [],
                    'sensoric'    => [],
                    'tumbling'    => [],
                    'aging'       => [],
                ],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        $filled = fn ($v) => $v !== null && $v !== '';

        // "14 ± 2" => [12, 16]; "12-13" => [12, 13]; angka tunggal => [n, n]
        $parseRange = function ($std) {
            if (!$std) {
                return null;
            }

            $std = (string) $std;
            $num = '\d+(?:[.,]\d+)?';
            $toFloat = fn ($n) => (float) str_replace(',', '.', $n);

            if (preg_match("/({$num})\s*(?:±|\+\/-|\+-)\s*({$num})/u", $std, $m)) {
                $center = $toFloat($m[1]);
                $tol    = $toFloat($m[2]);

                return [$center - $tol, $center + $tol];
            }

            preg_match_all("/{$num}/", $std, $m);
            $nums = array_map($toFloat, $m[0]);

            if (count($nums) === 0) {
                return null;
            }
            if (count($nums) === 1) {
                return [$nums[0], $nums[0]];
            }

            return [min($nums[0], $nums[1]), max($nums[0], $nums[1])];
        };

        $stdField = 'standard_mixture_temp';
        $keys     = ['actual_mixture_temp_1', 'actual_mixture_temp_2', 'actual_mixture_temp_3'];

        $outOfRange = function ($row, $key) use ($stdField, $filled, $parseRange) {
            $range = $parseRange($row->{$stdField});
            if (!$range || !$filled($row->{$key})) {
                return false;
            }
            $v = (float) $row->{$key};

            return $v < $range[0] - 0.0001 || $v > $range[1] + 0.0001;
        };

        // nilai pengganti: titik tengah standar (14 ± 2 => 14)
        $target = function ($row) use ($stdField, $parseRange) {
            $range = $parseRange($row->{$stdField});

            return round(($range[0] + $range[1]) / 2, 1);
        };

        $emulsifying = [];

        foreach ($keys as $key) {
            $emulsifying[$key] = [
                'when' => fn ($row) => $outOfRange($row, $key),
                'ok'   => $target,
            ];
        }

        // rata-rata dihitung ulang dari aktual yang terisi (setelah koreksi)
        $emulsifying['average_mixture_temp'] = [
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

                return round(array_sum($values) / count($values), 1);
            },
        ];

        return [
            'detail.items' => [
                'sensory' => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
            ],
            'detail.emulsifying' => $emulsifying,
            'detail.sensoric' => [
                'homogeneous'    => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
                'stiffness'      => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
                'aroma'          => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
                'foreign_object' => ['bad' => ['Terdeteksi'], 'ok' => 'Tidak Terdeteksi'],
            ],
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
        static::addGlobalScope(new UserAreaScope);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }

    // ReportProcessProd.php
    public function detail()
    {
        return $this->hasMany(DetailProcessProd::class, 'report_uuid', 'uuid');
    }

}