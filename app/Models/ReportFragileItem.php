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

class ReportFragileItem extends Model implements Auditable, ImportableModel, CustomImportRow
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;
    use ParsesExcelValues;

    protected $table = 'report_fragile_items';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'created_by',
        'known_by',
        'approved_by',
        'approved_at',
        'is_audit', 'source_uuid'
    ];

    protected $auditEvents = [
        'updated',
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
                'details'       => [],
                'detailManuals' => [],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        return [
            'details' => [
                'time_start' => ['bad' => [0, '0', false], 'ok' => 1],
                // time_end dinonaktifkan saat create (baru diisi saat edit), jadi bisa tersimpan null
                'time_end'   => ['bad' => [0, '0', false, null], 'ok' => 1],
                'notes'      => ['bad' => [0, '0', false], 'ok' => 1],
            ],
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailFragileItem::class, 'report_fragile_item_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }

    public function detailManuals()
    {
        return $this->hasMany(DetailFragileItemManual::class, 'report_fragile_item_uuid', 'uuid');
    }

        /* ===================== IMPORT EXCEL ===================== */

    public const CONDITIONS = ['Baik', 'Rusak'];

    // Peta "batch upload + (tanggal|shift)" => uuid laporan
    private static array $importReportUuids = [];

    private static ?array $fragileCache = null;
    private static ?array $sectionCache = null;

    // SAMAKAN query dan urutannya dengan controller yang mengirim $fragileItems ke form create
    private static function fragileOptions(): array
    {
        if (self::$fragileCache === null) {
            self::$fragileCache = FragileItem::orderBy('section_name')->orderBy('id')->get()
                ->map(fn ($f) => [
                    'uuid'    => $f->uuid,
                    'section' => (string) $f->section_name,
                    'name'    => (string) $f->item_name,
                    'owner'   => (string) $f->owner,
                    'qty'     => $f->quantity,
                ])->all();
        }
        return self::$fragileCache;
    }

    // SAMAKAN query dengan variabel $sections di form create
    private static function sectionOptions(): array
    {
        if (self::$sectionCache === null) {
            self::$sectionCache = Section::orderBy('section_name')->get()
                ->map(fn ($s) => ['name' => $s->section_name, 'uuid' => $s->uuid])
                ->all();
        }
        return self::$sectionCache;
    }

    private static function norm($v): string
    {
        return mb_strtolower(trim((string) $v));
    }

    public static function getImportMapping(): array
    {
        return [
            // Laporan
            'Tanggal'                  => 'date',
            'Shift'                    => 'shift',
            // Barang master (sudah terisi di template; isi Waktu Awal dan Keterangan)
            'Area'                     => 'section_name',
            'Nama Barang'              => 'item_name',
            'Pemilik'                  => 'owner',
            'Jumlah'                   => 'quantity',
            'Waktu Awal'               => 'time_start',
            'Keterangan'               => 'check_notes',
            // Barang manual (kosongkan Nama Barang bila mengisi bagian ini)
            'Area Manual'              => 'manual_section',
            'Sub Area Manual'          => 'manual_sub_area',
            'Nama Barang Manual'       => 'manual_item_name',
            'Jumlah Manual'            => 'manual_quantity',
            'Kondisi Manual'           => 'manual_condition',
            'Nama Karyawan'            => 'manual_employee',
            'Temuan Ketidaksesuaian'   => 'manual_issue_notes',
            'Tindakan Koreksi Manual'  => 'manual_corrective_action',
        ];
    }

    public static function getImportRules(): array
    {
        $areaUuid = auth()->user()->area_uuid;

        return [
            // dari controller
            'import_batch' => 'required',
            'area_uuid'    => 'required',
            'created_by'   => 'required|string',

            // laporan (dari Excel)
            'date'     => 'required|date',
            'shift'    => 'required|string|max:50',
            'row_type' => 'required|in:master,manual',

            // barang master
            'fragile_item_uuid' => 'required_if:row_type,master',
            'time_start'        => 'nullable|boolean',
            'check_notes'       => 'nullable|boolean',

            // barang manual
            'manual_item_name'         => 'required_if:row_type,manual|nullable|string|max:255',
            'manual_section_uuid'      => ['nullable', Rule::exists('sections', 'uuid')->where('area_uuid', $areaUuid)],
            'manual_sub_area'          => 'nullable|string|max:255',
            'manual_quantity'          => 'nullable|integer|min:0',
            'manual_condition'         => ['nullable', Rule::in(['baik', 'rusak'])],
            'manual_employee'          => 'nullable|string|max:255',
            'manual_issue_notes'       => 'nullable|string',
            'manual_corrective_action' => 'nullable|string',
        ];
    }

    public static function getMasterDataFields(): array
    {
        $check = ['options' => ['✓', 'x']];

        return [
            'time_start'  => ['label' => 'Waktu Awal (✓ / x)'] + $check,
            'check_notes' => ['label' => 'Keterangan (✓ / x)'] + $check,
            'manual_section' => [
                'label'    => 'Area Manual',
                'callback' => fn () => array_column(self::sectionOptions(), 'name'),
            ],
            'manual_condition' => ['label' => 'Kondisi Manual', 'options' => self::CONDITIONS],
        ];
    }

    // Template terisi otomatis: satu baris per barang master, centang awal x (belum dicentang)
    public static function getImportPrefillRows(): array
    {
        $date  = now()->toDateString();
        $shift = self::defaultShift();

        return array_map(fn ($f) => [
            'date'         => $date,
            'shift'        => $shift,
            'section_name' => $f['section'],
            'item_name'    => $f['name'],
            'owner'        => $f['owner'],
            'quantity'     => $f['qty'],
            'time_start'   => 'x',
            'check_notes'  => 'x',
        ], self::fragileOptions());
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

        $hasMaster = isset($row['item_name']);
        $hasManual = isset($row['manual_item_name']);

        if ($hasMaster && $hasManual) {
            throw new \RuntimeException('Isi salah satu saja: Nama Barang atau Nama Barang Manual.');
        }
        if (!$hasMaster && !$hasManual) {
            throw new \RuntimeException('Nama Barang atau Nama Barang Manual wajib diisi.');
        }

        if ($hasMaster) {
            $row['row_type'] = 'master';
            $name = trim((string) $row['item_name']);

            // Cocokkan nama + area + pemilik; bila masih kembar, persempit dengan jumlah
            $matches = array_values(array_filter(self::fragileOptions(), fn ($f) =>
                self::norm($f['name']) === self::norm($name)
                && self::norm($f['section']) === self::norm($row['section_name'] ?? '')
                && self::norm($f['owner']) === self::norm($row['owner'] ?? '')
            ));

            if (count($matches) > 1 && isset($row['quantity']) && is_numeric($row['quantity'])) {
                $byQty = array_values(array_filter($matches, fn ($f) =>
                    is_numeric($f['qty']) && (float) $f['qty'] === (float) $row['quantity']
                ));
                if ($byQty) {
                    $matches = $byQty;
                }
            }

            if (count($matches) === 0) {
                throw new \RuntimeException("Barang \"{$name}\" tidak ditemukan di master. Jangan ubah kolom Area, Nama Barang, dan Pemilik.");
            }
            if (count($matches) > 1) {
                throw new \RuntimeException("Barang \"{$name}\" ganda, tidak bisa dibedakan.");
            }

            $row['fragile_item_uuid'] = $matches[0]['uuid'];
            $row['time_start']  = isset($row['time_start']) ? self::toBool($row['time_start'], 'Waktu Awal') : false;
            $row['check_notes'] = isset($row['check_notes']) ? self::toBool($row['check_notes'], 'Keterangan') : false;

            return $row;
        }

        // Barang manual
        $row['row_type'] = 'manual';

        if (isset($row['manual_section'])) {
            $sec   = trim((string) $row['manual_section']);
            $found = collect(self::sectionOptions())->first(fn ($o) => self::norm($o['name']) === self::norm($sec));

            if (!$found) {
                throw new \RuntimeException("Area Manual \"{$sec}\" tidak ditemukan.");
            }
            $row['manual_section_uuid'] = $found['uuid'];
        }

        if (isset($row['manual_condition'])) {
            $row['manual_condition'] = mb_strtolower(self::matchOption((string) $row['manual_condition'], self::CONDITIONS));
        }

        if (isset($row['manual_quantity'])) {
            $q = $row['manual_quantity'];
            if (is_float($q) && floor($q) === $q) {
                $q = (int) $q;
            }
            $row['manual_quantity'] = $q;
        }

        foreach (['manual_sub_area', 'manual_item_name', 'manual_employee', 'manual_issue_notes', 'manual_corrective_action'] as $key) {
            if (isset($row[$key])) {
                $row[$key] = (string) $row[$key];
            }
        }

        return $row;
    }

    public static function importRow(array $row): void
    {
        // Laporan: satu per kombinasi tanggal + shift dalam SATU upload
        $key  = $row['date'] . '|' . $row['shift'];
        $uuid = self::$importReportUuids[$row['import_batch']][$key] ??= (string) Str::uuid();

        // firstOrCreate: bila baris pertama gagal dan dibatalkan, baris berikutnya membuatnya ulang
        $report = static::firstOrCreate(
            ['uuid' => $uuid],
            [
                'area_uuid'  => $row['area_uuid'],
                'date'       => $row['date'],
                'shift'      => $row['shift'],
                'created_by' => $row['created_by'],
            ]
        );

        if ($row['row_type'] === 'master') {
            // Satu barang master hanya sekali per laporan
            if (DetailFragileItem::where('report_fragile_item_uuid', $report->uuid)
                ->where('fragile_item_uuid', $row['fragile_item_uuid'])->exists()) {
                throw new \RuntimeException('Barang ini sudah ada di laporan dengan tanggal dan shift yang sama.');
            }

            DetailFragileItem::create([
                'report_fragile_item_uuid' => $report->uuid,
                'fragile_item_uuid'        => $row['fragile_item_uuid'],
                'time_start'               => $row['time_start'] ? '1' : '0',
                'time_end'                 => '0', // diisi saat edit, sama dengan form create
                'notes'                    => $row['check_notes'] ? '1' : '0',
            ]);

            return;
        }

        DetailFragileItemManual::create([
            'report_fragile_item_uuid' => $report->uuid,
            'section_uuid'             => $row['manual_section_uuid'] ?? null,
            'sub_area'                 => $row['manual_sub_area'] ?? null,
            'item_name'                => $row['manual_item_name'],
            'quantity'                 => $row['manual_quantity'] ?? 0,
            'condition'                => $row['manual_condition'] ?? null,
            'employee_name'            => $row['manual_employee'] ?? null,
            'issue_notes'              => $row['manual_issue_notes'] ?? null,
            'corrective_action'        => $row['manual_corrective_action'] ?? null,
        ]);
    }
}