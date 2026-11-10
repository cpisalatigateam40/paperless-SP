<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Scopes\UserAreaScope;

class MasterPasteurStep extends Model
{
    // Satu sumber daftar step, dipakai master & (nanti) form, supaya tidak hardcode di Blade
    public const STEPS = [
        1 => 'Water Injection',
        2 => 'Up Temperature',
        3 => 'Pasteurisasi',
        4 => 'Hot Water Recycling',
        5 => 'Cooling Water Injection',
        6 => 'Cooling Constant Temp.',
        7 => 'Raw Cooling Water',
        8 => 'Drainage Pressure',
    ];

    protected $fillable = [
        'uuid','master_uuid','step_order','step_name',
        'set_time','water_temp_min','water_temp_max','pressure_min','pressure_max',
    ];

    protected static function booted()
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
    }

    public function master() { return $this->belongsTo(MasterPasteur::class, 'master_uuid', 'uuid'); }

    // MasterPasteurStep
    public static function fmt($v): ?string
    {
        return is_null($v) ? null : rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.');
    }

    public static function rangeLabel($min, $max): string
    {
        if (is_null($min)) return '-';
        return self::fmt($min) . (is_null($max) ? '' : ' – ' . self::fmt($max));
    }

    public function getWaterTempLabelAttribute(): string
    {
        return self::rangeLabel($this->water_temp_min, $this->water_temp_max);
    }

    public function getPressureLabelAttribute(): string
    {
        return self::rangeLabel($this->pressure_min, $this->pressure_max);
    }

    public static function outOfStd($val, $min, $max, float $tol = 0.001): bool
    {
        if (is_null($val) || $val === '' || is_null($min)) return false;
        $val = (float) $val;
        $min = (float) $min;
        if (is_null($max)) return abs($val - $min) > $tol;
        return $val < $min - $tol || $val > (float) $max + $tol;
    }
}