<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportWaterbath extends Model
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }

    protected $table = 'report_waterbaths';
    
    protected $fillable = [
        'uuid', 'area_uuid', 'date', 'shift', 'created_by',
        'known_by', 'approved_by', 'approved_at', 'is_audit',
        'source_uuid',
        'form_version', 'product_uuid', 'gramase',   // <-- baru
    ];

    protected $casts = [
        'is_audit'     => 'boolean',
        'form_version' => 'integer',
        'gramase'      => 'decimal:2',
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

            // Form baru (all area, berbasis master)
            if ((int) $this->form_version >= 2) {
                $this->copyV2Children($clone);

                return $clone;
            }

            // Form lama: tidak berubah
            $this->copyChildren($this, $clone, [
                'details'       => [],
                'pasteurisasi'  => [],
                'coolingShocks' => [],
                'drippings'     => [],
            ]);

            return $clone;
        });
    }

    private function copyV2Children(self $clone): void
    {
        // jaga-jaga kalau copyHeaderToAudit tidak ikut menyalin kolom form baru
        $clone->forceFill([
            'form_version' => $this->form_version,
            'product_uuid' => $this->product_uuid,
            'gramase'      => $this->gramase,
        ])->save();

        $this->loadMissing(['batches', 'runs.parameters']);

        foreach ($this->batches as $batch) {
            $newBatch = $batch->replicate();
            $newBatch->uuid        = (string) Str::uuid();
            $newBatch->report_uuid = $clone->uuid;
            $newBatch->save();
        }

        foreach ($this->runs as $run) {
            $newRun = $run->replicate();
            $newRun->uuid        = (string) Str::uuid();
            $newRun->report_uuid = $clone->uuid;
            $newRun->save();

            foreach ($run->parameters as $param) {
                $newParam = $param->replicate();
                $newParam->uuid     = (string) Str::uuid();
                $newParam->run_uuid = $newRun->uuid;
                $newParam->save();
            }
        }
    }

    protected function auditNormalizeRules(): array
    {
        $filled = fn ($v) => $v !== null && $v !== '' && is_numeric($v);

        // Nilai aktual harus berada dalam standar (snapshot) di baris yang sama.
        // Di luar range -> diganti titik tengah range; kalau Max kosong, sama dengan Min.
        $rangeRule = function (string $actual, string $min, ?string $max = null, int $precision = 2) use ($filled) {
            $bounds = function ($row) use ($min, $max, $filled) {
                if (!$filled($row->{$min})) {
                    return null;
                }

                $lo = (float) $row->{$min};
                $hi = ($max && $filled($row->{$max})) ? (float) $row->{$max} : $lo;

                return [min($lo, $hi), max($lo, $hi)];
            };

            return [
                'when' => function ($row) use ($actual, $bounds, $filled) {
                    $r = $bounds($row);

                    if (!$r || !$filled($row->{$actual})) {
                        return false;
                    }

                    $v = (float) $row->{$actual};

                    return $v < $r[0] - 0.0001 || $v > $r[1] + 0.0001;
                },
                'ok' => function ($row) use ($bounds, $precision) {
                    $r = $bounds($row);

                    return round(($r[0] + $r[1]) / 2, $precision);
                },
            ];
        };

        // Aturan form lama (suhu air aktual harus sama dengan setting)
        $legacyRule = [
            'water_temp_actual' => [
                'when' => fn ($row) =>
                    is_numeric($row->water_temp_setting)
                    && is_numeric($row->water_temp_actual)
                    && abs((float) $row->water_temp_actual - (float) $row->water_temp_setting) > 0.0001,
                'ok'   => fn ($row) => $row->water_temp_setting,
            ],
        ];

        return [
            // ---- form baru (v2) ----
            'runs' => [
                'initial_temp' => $rangeRule('initial_temp', 'initial_temp_min', 'initial_temp_max'),

                // Sensori "Tidak Ok" (false) -> "Ok" (true); null dibiarkan
                'sensor_ok' => [
                    'when' => fn ($row) => $row->sensor_ok !== null && !(bool) $row->sensor_ok,
                    'ok'   => fn ($row) => true,
                ],
            ],
            'runs.parameters' => [
                'act_temp'      => $rangeRule('act_temp', 'set_temp_min', 'set_temp_max'),
                'cold_zone_act' => $rangeRule('cold_zone_act', 'cold_zone_set_min', 'cold_zone_set_max'),
                'act_time'      => $rangeRule('act_time', 'set_time'),
                'act_ct'        => $rangeRule('act_ct', 'std_ct_min', 'std_ct_max'),
            ],

            // ---- form lama ----
            'pasteurisasi'  => $legacyRule,
            'coolingShocks' => $legacyRule,
        ];
    }

    // Relasi ke Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    // Relasi ke detail produk
    public function details()
    {
        return $this->hasMany(DetailWaterbath::class, 'report_uuid', 'uuid');
    }

    // Relasi ke pasteurisasi
    public function pasteurisasi()
    {
        return $this->hasMany(PasteurisasiWaterbath::class, 'report_uuid', 'uuid');
    }

    // Relasi ke cooling shock
    public function coolingShocks()
    {
        return $this->hasMany(CoolingShockWaterbath::class, 'report_uuid', 'uuid');
    }

    // Relasi ke dripping
    public function drippings()
    {
        return $this->hasMany(DrippingWaterbath::class, 'report_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);

        // Form baru tidak punya FK cascade, jadi anak-anaknya dihapus manual.
        // Form lama tetap mengandalkan cascade di database.
        static::deleting(function (self $report) {
            if ((int) $report->form_version < 2) {
                return;
            }

            $runUuids = RunWaterbath::where('report_uuid', $report->uuid)->pluck('uuid');

            RunParameterWaterbath::whereIn('run_uuid', $runUuids)->delete();
            RunWaterbath::where('report_uuid', $report->uuid)->delete();
            BatchWaterbath::where('report_uuid', $report->uuid)->delete();
        });
    }

    // Relasi baru (form versi 2)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function batches()
    {
        return $this->hasMany(BatchWaterbath::class, 'report_uuid', 'uuid');
    }

    public function runs()
    {
        return $this->hasMany(RunWaterbath::class, 'report_uuid', 'uuid')->orderBy('run_number');
    }

    public function getIsLegacyFormAttribute(): bool
    {
        return (int) $this->form_version === 1;
    }

    public function scopeLegacy($q)  { return $q->where('form_version', 1); }
    public function scopeMaster($q)  { return $q->where('form_version', 2); }
    }