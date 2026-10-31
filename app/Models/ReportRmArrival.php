<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Excel\Contracts\CustomImportRow;
use App\Services\Excel\Contracts\ImportableModel;
use Illuminate\Validation\Rule;

class ReportRmArrival extends Model implements Auditable, ImportableModel, CustomImportRow
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;

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
        'date' => 'date',
        'is_audit' => 'boolean',
    ];

    protected function auditNormalizeRules(): array
    {
        return [
            'details' => [
                'packaging_condition' => ['bad' => ['x'], 'ok' => '✓'],
                'sensory_appearance'  => ['bad' => ['x'], 'ok' => '✓'],
                'sensory_aroma'       => ['bad' => ['x'], 'ok' => '✓'],
                'sensory_color'       => ['bad' => ['x'], 'ok' => '✓'],
                'contamination'       => ['bad' => ['x'], 'ok' => '✓'],
                'status'              => ['bad' => ['Tidak OK'], 'ok' => 'OK'],
            ],
        ];
    }

    public function copyToAudit(): self
    {
        return DB::transaction(function () {
            $clone = $this->copyHeaderToAudit();

            foreach ($this->details as $detail) {
                $copy = $detail->replicate();
                $copy->uuid = (string) Str::uuid();
                $copy->report_uuid = $clone->uuid;
                $copy->save();
            }
            return $clone;
        });
    }

    protected function auditBooleanResetFields(): array
    {
        return [];
    }

    protected function auditNullableResetFields(): array
    {
        return ['known_by', 'approved_by', 'approved_at'];
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
        return $this->hasMany(DetailRmArrival::class, 'report_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }

        /* ===================== IMPORT EXCEL ===================== */

    public const RM_CONDITIONS = ['Fresh (F)', 'Thawing (Th)', 'Frozen (Fr)', 'Dry'];

    // Sama dengan $supplierMap di view create
    public const SUPPLIER_MAP = [
        'Bandung'           => ['Bandung', 'Majalengka', 'Salatiga', 'Cikande', 'Banyumas'],
        'Cikande 1'         => ['Cikande 1', 'Cikande 3', 'Bandung', 'Banyumas', 'Pemalang', 'Sragen', 'Madiun', 'Majalengka', 'Mojokerto', 'Salatiga', 'Bondowoso'],
        'Medan'             => ['Cikande 1', 'Cikande 3', 'Bandung', 'Banyumas', 'Pemalang', 'Sragen', 'Madiun', 'Majalengka', 'Ngoro', 'Bondowoso', 'Salatiga', 'Medan'],
        'Ngoro - Mojokerto' => ['Ngoro', 'Madiun', 'Bondowoso', 'Majalengka'],
        'Salatiga'          => ['Salatiga', 'Pemalang', 'Sragen', 'Madiun', 'Banyumas'],
    ];

    // Sama dengan getSupplierFromPlantCode() di JavaScript form create
    public const PLANT_MAP = [
        '0' => 'Eksternal', '1' => 'Cikande 1', '2' => 'Ngoro', '3' => 'Salatiga',
        '5' => 'Bali', '6' => 'Medan', '7' => 'Berbek', '8' => 'Bandung', '9' => 'Karawang',
        'O' => 'Cikande 3', 'A' => 'Banyumas', 'B' => 'Palembang', 'C' => 'Makassar',
        'D' => 'Majalengka', 'E' => 'Sragen', 'F' => 'Bondowoso', 'G' => 'Pemalang', 'H' => 'Madiun',
    ];

    public static function suppliersForArea(): array
    {
        $areaName = auth()->user()->area->name ?? null;
        return self::SUPPLIER_MAP[$areaName] ?? [];
    }

    // Daftar bahan: SAMAKAN query dan urutannya dengan ReportRmArrivalController@create
    private static ?array $materialOptions = null;

    private static function materialOptions(): array
    {
        if (self::$materialOptions === null) {
            $list = [];
            foreach (RawMaterial::orderBy('material_name')->get() as $m) {
                $list[] = ['label' => $m->material_name, 'uuid' => $m->uuid, 'type' => 'raw'];
            }
            foreach (Premix::orderBy('name')->get() as $p) {
                $list[] = ['label' => $p->name . ' (Premix)', 'uuid' => $p->uuid, 'type' => 'premix'];
            }
            self::$materialOptions = $list;
        }
        return self::$materialOptions;
    }

    public static function getImportMapping(): array
    {
        return [
            'Bahan Baku'         => 'material_uuid',
            'Kondisi RM'         => 'rm_condition',
            'Produsen'           => 'supplier',
            'Kode Produksi'      => 'production_code',
            'Jam'                => 'time',
            'Suhu (°C)'          => 'temperature',
            'Kemasan'            => 'packaging_condition',
            'Sensory Kenampakan' => 'sensory_appearance',
            'Sensory Aroma'      => 'sensory_aroma',
            'Sensory Warna'      => 'sensory_color',
            'Kontaminasi'        => 'contamination',
            'Status'             => 'status',
            'Tindakan Koreksi'   => 'corrective_action',
            'Catatan'            => 'problem',
        ];
    }

    public static function getImportRules(): array
    {
        $areaUuid = auth()->user()->area_uuid;
        $check    = 'required|in:✓,x';

        return [
            // header (dari getAdditionalImportData di controller)
            'report_uuid'  => 'required',
            'area_uuid'    => 'required',
            'date'         => 'required|date',
            'shift'        => 'required|string|max:50',
            'created_by'   => 'required|string',
            'section_uuid' => ['required', Rule::exists('sections', 'uuid')->where('area_uuid', $areaUuid)],

            // detail (dari Excel)
            'material_uuid'       => 'required',
            'material_type'       => 'required|in:raw,premix',
            'rm_condition'        => ['required', Rule::in(self::RM_CONDITIONS)],
            'supplier'            => 'nullable|string|max:255',
            'production_code'     => 'required|string|max:100',
            'time'                => 'required|date_format:H:i',
            'temperature'         => 'nullable|numeric',
            'packaging_condition' => $check,
            'sensory_appearance'  => $check,
            'sensory_aroma'       => $check,
            'sensory_color'       => $check,
            'contamination'       => $check,
            'status'              => 'required|in:OK,Tidak OK',
            'corrective_action'   => 'nullable|string',
            'problem'             => 'nullable|string',
        ];
    }

    public static function getMasterDataFields(): array
    {
        $check = ['label' => 'Hasil (✓ / x)', 'options' => ['✓', 'x']];

        return [
            'material_uuid' => [
                'label'    => 'Bahan Baku',
                'callback' => fn () => array_column(self::materialOptions(), 'label'),
            ],
            'rm_condition' => ['label' => 'Kondisi RM', 'options' => self::RM_CONDITIONS],
            'supplier'     => ['label' => 'Produsen', 'callback' => fn () => self::suppliersForArea()],
            'packaging_condition' => ['label' => 'Kemasan'] + $check,
            'sensory_appearance'  => ['label' => 'Sensory Kenampakan'] + $check,
            'sensory_aroma'       => ['label' => 'Sensory Aroma'] + $check,
            'sensory_color'       => ['label' => 'Sensory Warna'] + $check,
            'contamination'       => ['label' => 'Kontaminasi'] + $check,
            'status'              => ['label' => 'Status', 'options' => ['OK', 'Tidak OK']],
        ];
    }

    // Baris contoh di template, terisi penuh
    public static function getImportPrefillRows(): array
    {
        $first = self::materialOptions()[0]['label'] ?? null;

        return [[
            'material_uuid'       => $first,
            'rm_condition'        => 'Fresh (F)',
            'supplier'            => self::suppliersForArea()[0] ?? null,
            'production_code'     => 'QC28101CC0',
            'time'                => '08:30',
            'temperature'         => 4.5,
            'packaging_condition' => '✓',
            'sensory_appearance'  => '✓',
            'sensory_aroma'       => '✓',
            'sensory_color'       => '✓',
            'contamination'       => '✓',
            'status'              => 'OK',
            'corrective_action'   => '',
            'problem'             => 'Contoh pengisian, timpa atau hapus baris ini',
        ]];
    }

    public static function transformImportRow(array $row): array
    {
        // Bahan: label -> uuid + type (tolak bila tidak ada / ganda)
        if (isset($row['material_uuid'])) {
            $name  = trim((string) $row['material_uuid']);
            $match = array_values(array_filter(
                self::materialOptions(),
                fn ($o) => mb_strtolower(trim($o['label'])) === mb_strtolower($name)
            ));

            if (count($match) === 0) {
                throw new \RuntimeException("Bahan \"{$name}\" tidak ditemukan.");
            }
            if (count($match) > 1) {
                throw new \RuntimeException("Bahan \"{$name}\" ganda, tidak bisa dibedakan.");
            }
            $row['material_uuid'] = $match[0]['uuid'];
            $row['material_type'] = $match[0]['type'];
        }

        // Kondisi RM: cocokkan tanpa peduli huruf besar/kecil
        if (isset($row['rm_condition'])) {
            $v = mb_strtolower(trim((string) $row['rm_condition']));
            foreach (self::RM_CONDITIONS as $opt) {
                if (mb_strtolower($opt) === $v) {
                    $row['rm_condition'] = $opt;
                    break;
                }
            }
        }

        // Teks yang bisa terbaca sebagai angka oleh Excel
        foreach (['production_code', 'corrective_action', 'problem'] as $key) {
            if (isset($row[$key])) {
                $row[$key] = (string) $row[$key];
            }
        }

        // Produsen: dari kolom (harus ada di daftar area), atau deteksi dari kode produksi
        $allowed = self::suppliersForArea();
        if (isset($row['supplier'])) {
            $given = mb_strtolower(trim((string) $row['supplier']));
            $found = collect($allowed)->first(fn ($a) => mb_strtolower(trim($a)) === $given);

            if (!$found) {
                throw new \RuntimeException("Produsen \"{$row['supplier']}\" tidak tersedia untuk area ini.");
            }
            $row['supplier'] = $found;
        } else {
            $row['supplier'] = self::supplierFromCode((string) ($row['production_code'] ?? ''), $allowed);
        }

        // Jam: serial Excel / teks -> "H:i"
        if (array_key_exists('time', $row)) {
            $time = self::toHi($row['time']);
            if ($time === null) {
                throw new \RuntimeException('Jam tidak valid.');
            }
            $row['time'] = $time;
        }

        // Centang: samakan ke ✓ / x
        foreach (['packaging_condition', 'sensory_appearance', 'sensory_aroma', 'sensory_color', 'contamination'] as $key) {
            if (isset($row[$key])) {
                $v = mb_strtolower(trim((string) $row[$key]));
                $row[$key] = match (true) {
                    in_array($v, ['✓', '√', '✔', 'v', 'ok'], true) => '✓',
                    in_array($v, ['x', '×', 'nok'], true)          => 'x',
                    default                                        => $v,
                };
            }
        }

        // Status: OK / Tidak OK
        if (isset($row['status'])) {
            $v = mb_strtolower(trim((string) $row['status']));
            $row['status'] = match (true) {
                $v === 'ok'                                  => 'OK',
                in_array($v, ['tidak ok', 'nok', 'x'], true) => 'Tidak OK',
                default                                      => trim((string) $row['status']),
            };
        }

        return $row;
    }

    public static function importRow(array $row): void
    {
        // Induk: dibuat sekali per upload (report_uuid sama untuk semua baris)
        $report = static::firstOrCreate(
            ['uuid' => $row['report_uuid']],
            [
                'area_uuid'    => $row['area_uuid'],
                'section_uuid' => $row['section_uuid'],
                'date'         => $row['date'],
                'shift'        => $row['shift'],
                'created_by'   => $row['created_by'],
            ]
        );

        DetailRmArrival::create([
            'uuid'                => (string) Str::uuid(),
            'report_uuid'         => $report->uuid,
            'material_uuid'       => $row['material_uuid'],
            'material_type'       => $row['material_type'],
            'raw_material_uuid'   => $row['material_type'] === 'raw' ? $row['material_uuid'] : null,
            'supplier'            => $row['supplier'] ?? null,
            'rm_condition'        => $row['rm_condition'],
            'production_code'     => $row['production_code'],
            'time'                => $row['time'],
            'temperature'         => $row['temperature'] ?? null,
            'packaging_condition' => $row['packaging_condition'],
            'sensory_appearance'  => $row['sensory_appearance'],
            'sensory_aroma'       => $row['sensory_aroma'],
            'sensory_color'       => $row['sensory_color'],
            'contamination'       => $row['contamination'],
            'problem'             => $row['problem'] ?? null,
            'corrective_action'   => $row['corrective_action'] ?? null,
            'status'              => $row['status'],
        ]);
    }

    // Sama dengan extractPlantCode() + checkSupplier() di JavaScript form create
    private static function supplierFromCode(string $code, array $allowed): ?string
    {
        if (!preg_match('/QC\d{2}([0-9A-HO])/i', $code, $m)) {
            return null;
        }
        $name = self::PLANT_MAP[strtoupper($m[1])] ?? null;
        if (!$name) {
            return null;
        }

        $candidates = match (mb_strtolower($name)) {
            'cikande 1' => ['cikande 1', 'cikande'],
            'ngoro'     => ['ngoro', 'ngoro - mojokerto', 'mojokerto'],
            default     => [mb_strtolower($name)],
        };

        foreach ($allowed as $a) {
            if (in_array(mb_strtolower(trim($a)), $candidates, true)) {
                return $a;
            }
        }
        return null;
    }

    // "08:30" / "8.30" / serial 0.3541666 / DateTime -> "H:i" (atau null)
    private static function toHi($v): ?string
    {
        if ($v === null || $v === '') return null;
        if ($v instanceof \DateTimeInterface) return $v->format('H:i');
        if (is_int($v) || is_float($v)) {
            $secs = (int) round(fmod((float) $v, 1) * 86400);
            return sprintf('%02d:%02d', intdiv($secs, 3600) % 24, intdiv($secs % 3600, 60));
        }
        if (preg_match('/^(\d{1,2})[:.](\d{2})/', trim((string) $v), $m) && $m[1] < 24 && $m[2] < 60) {
            return sprintf('%02d:%02d', $m[1], $m[2]);
        }
        return null;
    }
}