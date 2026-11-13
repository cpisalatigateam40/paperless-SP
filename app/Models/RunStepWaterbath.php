<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class RunStepWaterbath extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public const PASTEURISASI = 'pasteurisasi';
    public const COOLING      = 'cooling';
    public const DRYING       = 'drying';

    public const PARAMETERS = [
        self::PASTEURISASI => 'Pasteurisasi',
        self::COOLING      => 'Cooling',
        self::DRYING       => 'Drying',
    ];

    // Urutan kolom di tabel + label
    public const FIELDS = [
        'start_time'                    => 'Start Process',
        'stop_time'                     => 'Stop Process',
        'initial_water_temp'            => 'Suhu Awal Air',
        'water_temp_after_input_panel'  => 'Suhu Air Setelah Produk Masuk (Panel)',
        'water_temp_after_input_thermo' => 'Suhu Air Setelah Produk Masuk (Thermo)',
        'water_temp_final'              => 'Suhu Akhir Air',
        'product_temp_final'            => 'Suhu Akhir Produk',
        'remark'                        => 'Keterangan',
    ];

    // Kolom yang boleh diisi per parameter (sisanya "Tidak untuk diisi")
    public const ALLOWED = [
        self::PASTEURISASI => [
            'start_time', 'stop_time', 'initial_water_temp',
            'water_temp_after_input_panel', 'water_temp_after_input_thermo',
            'water_temp_final', 'product_temp_final', 'remark',
        ],
        self::COOLING => [
            'start_time', 'stop_time', 'initial_water_temp', 'product_temp_final', 'remark',
        ],
        self::DRYING => [
            'start_time', 'stop_time', 'product_temp_final', 'remark',
        ],
    ];

    protected $table = 'run_step_waterbaths';
    protected $fillable = [
        'uuid', 'run_uuid', 'parameter',
        'start_time', 'stop_time',
        'initial_water_temp', 'water_temp_after_input_panel', 'water_temp_after_input_thermo',
        'water_temp_final', 'product_temp_final', 'remark',
    ];

    protected $casts = [
        'initial_water_temp'            => 'decimal:2',
        'water_temp_after_input_panel'  => 'decimal:2',
        'water_temp_after_input_thermo' => 'decimal:2',
        'water_temp_final'              => 'decimal:2',
        'product_temp_final'            => 'decimal:2',
    ];

    protected $auditEvents = ['updated'];

    public function run()
    {
        return $this->belongsTo(RunWaterbath::class, 'run_uuid', 'uuid');
    }

    public function getLabelAttribute(): string
    {
        return self::PARAMETERS[$this->parameter] ?? $this->parameter;
    }

    public static function allows(string $parameter, string $field): bool
    {
        return in_array($field, self::ALLOWED[$parameter] ?? [], true);
    }

    // "14:30:00" -> "14:30" (untuk input type=time dan tampilan)
    public static function hm($value): ?string
    {
        return filled($value) ? substr((string) $value, 0, 5) : null;
    }
}