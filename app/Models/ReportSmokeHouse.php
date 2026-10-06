<?php

namespace App\Models;

use App\Scopes\UserAreaScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;

class ReportSmokeHouse extends Model implements Auditable
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

    protected $table = 'report_smoke_houses';

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
                    'steps'     => [],
                    'sensories' => [],
                    'reworks'   => [
                        'steps' => [],
                    ],
                ],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        $filled = fn ($v) => $v !== null && $v !== '';

        // "75 - 85" => [75, 85]; "80 ± 2" => [78, 82]; angka tunggal => [n, n]
        $parseRange = function ($std) {
            if (!is_string($std) && !is_numeric($std)) {
                return null;
            }

            $std = (string) $std;
            $num = '\d+(?:[.,]\d+)?';
            $toFloat = fn ($n) => (float) str_replace(',', '.', $n);

            if (preg_match("/({$num})\s*(?:±|\+\/-|\+-)\s*({$num})/u", $std, $m)) {
                return [$toFloat($m[1]) - $toFloat($m[2]), $toFloat($m[1]) + $toFloat($m[2])];
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

        // aktual harus berada dalam standar setting pada baris yang sama
        $rule = function (string $actual, string $setting, int $precision = 2) use ($filled, $parseRange) {
            return [
                'when' => function ($row) use ($actual, $setting, $filled, $parseRange) {
                    $r = $parseRange($row->{$setting});
                    if (!$r || !$filled($row->{$actual}) || !is_numeric($row->{$actual})) {
                        return false;
                    }
                    $v = (float) $row->{$actual};

                    return $v < $r[0] - 0.0001 || $v > $r[1] + 0.0001;
                },
                'ok' => function ($row) use ($setting, $parseRange, $precision) {
                    $r = $parseRange($row->{$setting});

                    return round(($r[0] + $r[1]) / 2, $precision);
                },
            ];
        };

        $stepRules = [
            'actual_temp' => $rule('actual_temp', 'setting_temp'),
            'actual_time' => $rule('actual_time', 'setting_time', 0),
            'actual_rh'   => $rule('actual_rh', 'setting_rh'),
            'actual_ct'   => $rule('actual_ct', 'setting_ct'),
        ];

        $passRule = ['bad' => ['Fail'], 'ok' => 'Pass'];

        return [
            'details.steps'         => $stepRules,
            'details.reworks.steps' => $stepRules,
            'details.sensories' => [
                'appearance' => $passRule,
                'color'      => $passRule,
                'aroma'      => $passRule,
                'taste'      => $passRule,
                'texture'    => $passRule,
            ],
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(
            DetailSmokeHouse::class,
            'report_uuid',
            'uuid'
        );
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'uuid');
    }
}