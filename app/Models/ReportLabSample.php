<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\UserAreaScope;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\HasAudit;
use Illuminate\Support\Facades\DB;
use App\Models\Traits\ParsesExcelValues;
use App\Services\Excel\Contracts\CustomImportRow;
use App\Services\Excel\Contracts\ImportableModel;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportLabSample extends Model implements Auditable, ImportableModel, CustomImportRow
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;
    use ParsesExcelValues;

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'storage',
        'created_by',
        'known_by',
        'accepted_by',
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
        return ['known_by', 'accepted_by', 'approved_by', 'approved_at'];
    }

    public function copyToAudit(): self
    {
        return DB::transaction(function () {
            $clone = $this->copyHeaderToAudit();

            $this->copyChildren($this, $clone, [
                'details' => [],
            ]);

            return $clone;
        });
    }

    // Relasi ke detail_lab_samples
    public function details()
    {
        return $this->hasMany(DetailLabSample::class, 'report_uuid', 'uuid');
    }

    // Relasi ke area (jika ada tabel areas)
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }

        /* ===================== IMPORT EXCEL ===================== */

    // Key internal => nilai yang tersimpan (sama dengan checkbox di form create)
    public const STORAGES = [
        'storage_frozen'  => 'Frozen (≤ -18°C)',
        'storage_chilled' => 'Chilled (0 - 5°C)',
        'storage_other'   => 'Other',
    ];
    public const SAMPLE_TYPES = ['Sampel Lab', 'Sampel Retained', 'Sampel RnD', 'Sampel Trial', 'Lainnya'];
    public const UNITS        = ['pcs', 'pack', 'inner', 'box'];

    // Peta "batch upload + (tanggal|shift|storage)" => uuid laporan
    private static array $importReportUuids = [];

    private static ?array $productCache = null;

    // SAMAKAN query dengan ReportLabSampleController@create (variabel $products di form)
    private static function productOptions(): array
    {
        if (self::$productCache === null) {
            self::$productCache = Product::uniqueByName()
                ->map(fn ($p) => ['name' => $p->product_name, 'uuid' => $p->uuid])
                ->all();
        }
        return self::$productCache;
    }

    // Cocokkan nilai Excel ke daftar resmi tanpa peduli huruf besar/kecil
    private static function matchOption(string $value, array $options): string
    {
        $v = mb_strtolower(trim($value));
        foreach ($options as $opt) {
            if (mb_strtolower($opt) === $v) {
                return $opt;
            }
        }
        return trim($value);
    }

    public static function getImportMapping(): array
    {
        $columns = [
            // Laporan
            'Tanggal' => 'date',
            'Shift'   => 'shift',
        ];

        // Sampel storage: satu kolom per pilihan (✓ / x)
        foreach (self::STORAGES as $key => $label) {
            $columns["Storage - {$label}"] = $key;
        }

        return $columns + [
            // Detail
            'Produk'        => 'product_uuid',
            'Gramase'       => 'gramase',
            'Kode Produksi' => 'production_code',
            'Best Before'   => 'best_before',
            'Jenis Sample'  => 'sample_type',
            'Jumlah'        => 'quantity',
            'Satuan'        => 'unit',
            'Catatan'       => 'notes',
        ];
    }

    public static function getImportRules(): array
    {
        return [
            // dari controller
            'import_batch' => 'required',
            'area_uuid'    => 'required',
            'created_by'   => 'required|string',

            // laporan (dari Excel)
            'date'    => 'required|date',
            'shift'   => 'required|string|max:50',
            'storage' => 'nullable|string',

            // detail (dari Excel)
            'product_uuid'    => 'required',
            'gramase'         => 'required|numeric|min:0',
            'production_code' => 'nullable|string|max:100',
            'best_before'     => 'nullable|date',
            'sample_type'     => ['required', Rule::in(self::SAMPLE_TYPES)],
            'quantity'        => 'nullable|integer|min:0',
            'unit'            => ['required', Rule::in(self::UNITS)],
            'notes'           => 'nullable|string',
        ];
    }

    public static function getMasterDataFields(): array
    {
        $fields = [
            'product_uuid' => [
                'label'    => 'Produk',
                'callback' => fn () => array_column(self::productOptions(), 'name'),
            ],
            'sample_type' => ['label' => 'Jenis Sample', 'options' => self::SAMPLE_TYPES],
            'unit'        => ['label' => 'Satuan', 'options' => self::UNITS],
        ];

        foreach (self::STORAGES as $key => $label) {
            $fields[$key] = ['label' => $label, 'options' => ['✓', 'x']];
        }

        return $fields;
    }

    // Baris contoh di template, terisi penuh
    public static function getImportPrefillRows(): array
    {
        return [[
            'date'            => now()->toDateString(),
            'shift'           => self::defaultShift(),
            'storage_frozen'  => '✓',
            'storage_chilled' => 'x',
            'storage_other'   => 'x',
            'product_uuid'    => self::productOptions()[0]['name'] ?? null,
            'gramase'         => 100,
            'production_code' => 'CONTOH-001',
            'best_before'     => now()->addYear()->toDateString(),
            'sample_type'     => 'Sampel Lab',
            'quantity'        => 5,
            'unit'            => 'pcs',
            'notes'           => 'Contoh pengisian, timpa atau hapus baris ini',
        ]];
    }

    public static function transformImportRow(array $row): array
    {
        // Tanggal
        if (array_key_exists('date', $row)) {
            $date = self::toDate($row['date']);
            if ($date === null) {
                throw new \RuntimeException('Tanggal tidak valid (contoh: 2026-10-27).');
            }
            $row['date'] = $date;
        }

        // Shift: kosong -> session (QC Inspector) atau NON-SHIFT
        $shift = isset($row['shift']) ? trim((string) $row['shift']) : '';
        $row['shift'] = $shift !== '' ? $shift : self::defaultShift();

        // Storage: ✓ / x per kolom -> teks gabungan (sama dengan implode(', ') di store())
        $selected = [];
        foreach (self::STORAGES as $key => $label) {
            if (isset($row[$key]) && self::toBool($row[$key], "Storage - {$label}")) {
                $selected[] = $label;
            }
            unset($row[$key]);
        }
        $row['storage'] = implode(', ', $selected);

        // Produk: nama -> uuid
        if (isset($row['product_uuid'])) {
            $name  = trim((string) $row['product_uuid']);
            $found = collect(self::productOptions())
                ->first(fn ($o) => mb_strtolower(trim($o['name'])) === mb_strtolower($name));

            if (!$found) {
                throw new \RuntimeException("Produk \"{$name}\" tidak ditemukan.");
            }
            $row['product_uuid'] = $found['uuid'];
        }

        // Kode produksi bisa terbaca sebagai angka oleh Excel
        if (isset($row['production_code'])) {
            $row['production_code'] = trim((string) $row['production_code']);
        }

        // Best before: dari kolom; bila kosong dihitung dari kode produksi (sama dengan form)
        if (array_key_exists('best_before', $row)) {
            $bb = self::toDate($row['best_before']);
            if ($bb === null) {
                throw new \RuntimeException('Best Before tidak valid (contoh: 2027-10-27).');
            }
            $row['best_before'] = $bb;
        } elseif (!empty($row['production_code'])) {
            $row['best_before'] = self::bestBeforeFromCode($row['production_code']);
        }

        // Pilihan tetap: samakan huruf besar/kecil dengan daftar resmi
        if (isset($row['sample_type'])) {
            $row['sample_type'] = self::matchOption((string) $row['sample_type'], self::SAMPLE_TYPES);
        }
        if (isset($row['unit'])) {
            $row['unit'] = self::matchOption((string) $row['unit'], self::UNITS);
        }

        // Angka bertulis koma desimal ("100,5") -> titik
        foreach (['gramase', 'quantity'] as $key) {
            if (isset($row[$key]) && is_string($row[$key])) {
                $row[$key] = str_replace(',', '.', trim($row[$key]));
            }
        }

        if (isset($row['notes'])) {
            $row['notes'] = (string) $row['notes'];
        }

        return $row;
    }

    public static function importRow(array $row): void
    {
        // Laporan: satu per kombinasi tanggal + shift + storage dalam SATU upload
        $key  = implode('|', [$row['date'], $row['shift'], $row['storage']]);
        $uuid = self::$importReportUuids[$row['import_batch']][$key] ??= (string) Str::uuid();

        // firstOrCreate: bila baris pertama gagal dan dibatalkan, baris berikutnya membuatnya ulang
        $report = static::firstOrCreate(
            ['uuid' => $uuid],
            [
                'area_uuid'  => $row['area_uuid'],
                'date'       => $row['date'],
                'shift'      => $row['shift'],
                'storage'    => $row['storage'],
                'created_by' => $row['created_by'],
            ]
        );

        DetailLabSample::create([
            'uuid'            => (string) Str::uuid(),
            'report_uuid'     => $report->uuid,
            'product_uuid'    => $row['product_uuid'],
            'production_code' => $row['production_code'] ?? null,
            'best_before'     => $row['best_before'] ?? null,
            'quantity'        => $row['quantity'] ?? null,
            'notes'           => $row['notes'] ?? null,
            'gramase'         => $row['gramase'],
            'sample_type'     => $row['sample_type'],
            'unit'            => $row['unit'],
        ]);
    }
}