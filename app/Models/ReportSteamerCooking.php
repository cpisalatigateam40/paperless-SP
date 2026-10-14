<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;


class ReportSteamerCooking extends Model
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }

    protected $table = 'report_steamer_cookings';

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'product_uuid',
        'product_code_range',
        'gramase',
        'notes',
        'curve_url',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'is_audit',
        'source_uuid',
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
                'batches' => [
                    'details' => [
                        'coreTemps' => [],
                    ],
                ],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        $filled = fn ($v) => $v !== null && $v !== '';

        // master dimuat sekali (lazy) per proses normalisasi, bukan per baris
        $master = null;
        $loaded = false;
        $getMaster = function () use (&$master, &$loaded) {
            if (!$loaded) {
                $base = \App\Models\SteamerStandard::withoutGlobalScopes()
                    ->where('product_uuid', $this->product_uuid);

                // utamakan master se-area, kalau tidak ada pakai master produk saja
                $master = (clone $base)->where('area_uuid', $this->area_uuid)->first()
                    ?? $base->first();
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

        $outOfRange = function ($row, string $field, string $prefix) use ($range, $filled) {
            $r = $range($prefix);
            if (!$r || !$filled($row->{$field})) {
                return false;
            }
            $v = (float) $row->{$field};

            return $v < $r[0] - 0.0001 || $v > $r[1] + 0.0001;
        };

        // nilai pengganti: titik tengah standar (precision 0 untuk input tanpa desimal)
        $rule = fn (string $field, string $prefix, int $precision = 2) => [
            'when' => fn ($row) => $outOfRange($row, $field, $prefix),
            'ok'   => function ($row) use ($range, $prefix, $precision) {
                $r = $range($prefix);

                return round(($r[0] + $r[1]) / 2, $precision);
            },
        ];

        $okRule = ['bad' => ['Tidak OK'], 'ok' => 'OK'];

        return [
            'batches.details' => [
                'setup_time' => $rule('setup_time', 'setup_time', 0),
                'room_temp'  => $rule('room_temp', 'room_temp'),
                'sensory_bentuk'  => $okRule,
                'sensory_warna'   => $okRule,
                'sensory_aroma'   => $okRule,
                'sensory_rasa'    => $okRule,
                'sensory_tekstur' => $okRule,
            ],
            'batches.details.coreTemps' => [
                'temp_value' => [
                    'when' => fn ($row) => $outOfRange($row, 'temp_value', 'core_temp'),
                    'ok'   => function ($row) use ($range) {
                        $r = $range('core_temp');

                        return round(($r[0] + $r[1]) / 2, 2);
                    },
                ],
            ],
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function batches()
    {
        return $this->hasMany(SteamerCookingBatch::class, 'report_uuid', 'uuid');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'uuid'); // sesuaikan kolom PK User
    }
}