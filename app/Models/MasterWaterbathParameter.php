<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class MasterWaterbathParameter extends Model
{
    use HasFactory;

    public const PASTEURISASI = 'pasteurisasi';
    public const COOLING      = 'cooling';
    public const DRYING       = 'drying';

    public const PARAMETERS = [
        self::PASTEURISASI => 'Pasteurization',
        self::COOLING      => 'Cooling',
        self::DRYING       => 'Drying',
    ];

    protected $table = 'master_waterbath_parameters';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'master_waterbath_uuid',
        'parameter',
        'set_temp_min',
        'set_temp_max',
        'cold_zone_temp_min',
        'cold_zone_temp_max',
        'set_time',
        'std_ct_min',
        'std_ct_max',
    ];

    protected $casts = [
        'set_temp_min'       => 'decimal:2',
        'set_temp_max'       => 'decimal:2',
        'cold_zone_temp_min' => 'decimal:2',
        'cold_zone_temp_max' => 'decimal:2',
        'set_time'           => 'decimal:2',
        'std_ct_min'         => 'decimal:2',
        'std_ct_max'         => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function master()
    {
        return $this->belongsTo(MasterWaterbath::class, 'master_waterbath_uuid', 'uuid');
    }

    public function getLabelAttribute(): string
    {
        return self::PARAMETERS[$this->parameter] ?? $this->parameter;
    }

    public function isDrying(): bool
    {
        return $this->parameter === self::DRYING;
    }

    // Untuk drying: set_temp_* dianggap HOT ZONE
    public function getSetTempRangeAttribute(): string
    {
        return self::formatRange($this->set_temp_min, $this->set_temp_max);
    }

    public function getColdZoneRangeAttribute(): string
    {
        return self::formatRange($this->cold_zone_temp_min, $this->cold_zone_temp_max);
    }

    public function getStdCtRangeAttribute(): string
    {
        return self::formatRange($this->std_ct_min, $this->std_ct_max);
    }

    /**
     * Format range 2 desimal.
     * max kosong / sama dengan min => tampil sebagai nilai tunggal.
     */
    public static function formatRange($min, $max): string
    {
        if (!is_numeric($min)) {
            return '-';
        }

        $minText = number_format((float) $min, 2, '.', '');

        if (!is_numeric($max) || abs((float) $max - (float) $min) < 0.0001) {
            return $minText;
        }

        return $minText . ' - ' . number_format((float) $max, 2, '.', '');
    }

    /**
     * Cek nilai di dalam range.
     * Return null kalau nilai kosong atau master belum diisi (tidak diberi warna).
     * Kalau max kosong, nilai harus sama dengan min.
     */
    public static function inRange($value, $min, $max): ?bool
    {
        if (!is_numeric($value) || !is_numeric($min)) {
            return null;
        }

        $value = (float) $value;
        $min   = (float) $min;

        if (!is_numeric($max)) {
            return abs($value - $min) < 0.0001;
        }

        return $value >= $min && $value <= (float) $max;
    }

    public static function fmt($value): ?string
    {
        return is_numeric($value) ? number_format((float) $value, 2, '.', '') : null;
    }
}