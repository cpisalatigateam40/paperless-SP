<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportFreezPackaging extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_freez_packagings';

    protected $fillable = [
        'uuid',
        'area_uuid',
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
        return ['known_by', 'approved_by', 'approved_at'];
    }

    public function copyToAudit(): self
    {
        return DB::transaction(function () {
            $clone = $this->copyHeaderToAudit();

            $this->copyChildren($this, $clone, [
                'details' => [
                    'freezing' => [
                        'actualTemps' => [],
                    ],
                    'kartoning'               => [],
                    'documentations'          => [],
                    'kartoningDocumentations' => [],
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

        // hanya aktual yang terisi DAN di luar standar
        $outOfRange = function ($row, $key) use ($filled, $parseRange) {
            $range = $parseRange($row->carton_weight_standard);
            if (!$range || !$filled($row->{$key})) {
                return false;
            }
            $v = (float) $row->{$key};

            return $v < $range[0] - 0.0001 || $v > $range[1] + 0.0001;
        };

        // nilai pengganti: titik tengah standar (12-13 => 12.5)
        $target = function ($row) use ($parseRange) {
            $range = $parseRange($row->carton_weight_standard);

            return round(($range[0] + $range[1]) / 2, 2);
        };

        $weightKeys = ['weight_1', 'weight_2', 'weight_3', 'weight_4', 'weight_5'];

        $kartoningRules = [
            'carton_condition' => ['bad' => ['x'], 'ok' => '✓'],
            'label_condition'  => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
        ];

        foreach ($weightKeys as $key) {
            $kartoningRules[$key] = [
                'when' => fn ($row) => $outOfRange($row, $key),
                'ok'   => $target,
            ];
        }

        // rata-rata dihitung ulang dari berat yang terisi (setelah koreksi)
        $kartoningRules['avg_weight'] = [
            'when' => function ($row) use ($weightKeys, $outOfRange) {
                foreach ($weightKeys as $key) {
                    if ($outOfRange($row, $key)) {
                        return true;
                    }
                }
                return false;
            },
            'ok' => function ($row) use ($weightKeys, $filled, $outOfRange, $target) {
                $values = [];
                foreach ($weightKeys as $key) {
                    if ($filled($row->{$key})) {
                        $values[] = $outOfRange($row, $key) ? $target($row) : (float) $row->{$key};
                    }
                }

                return round(array_sum($values) / count($values), 2);
            },
        ];

        // suhu aktual produk harus sama dengan standar suhu (data_freezings.standard_temp)
        $tempDiffers = function ($row) {
            $std = optional($row->freezing)->standard_temp;

            return is_numeric($std)
                && is_numeric($row->actual_temp)
                && abs((float) $row->actual_temp - (float) $std) > 0.0001;
        };

        return [
            'details' => [
                'release_status' => ['bad' => ['Hold'], 'ok' => 'Release'],
            ],
            'details.freezing.actualTemps' => [
                'actual_temp' => [
                    'when' => $tempDiffers,
                    'ok'   => fn ($row) => $row->freezing->standard_temp,
                ],
            ],
            'details.kartoning' => $kartoningRules,
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailFreezPackaging::class, 'report_uuid', 'uuid');
    }
}