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

class ReportProcessAreaCleanliness extends Model implements Auditable, ImportableModel, CustomImportRow
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;
    use ParsesExcelValues;

    protected $table = 'report_process_area_cleanliness';

    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'section_name',
        'created_by',
        'known_by',
        'approved_by',
        'is_audit', 'source_uuid'
    ];

    protected $auditEvents = [
        'updated',
    ];

    protected $casts = ['is_audit' => 'boolean'];

    protected function auditBooleanResetFields(): array
    {
        return [];
    }

    protected function auditNullableResetFields(): array
    {
        return ['known_by', 'approved_by'];
    }

    public function copyToAudit(): self
    {
        return DB::transaction(function () {
            $clone = $this->copyHeaderToAudit();

            $this->copyChildren($this, $clone, [
                'details' => [
                    'items' => [
                        'followups' => [],
                    ],
                ],
            ]);

            return $clone;
        });
    }

    protected function auditNormalizeRules(): array
    {
        return [
            'details.items' => [
                'condition'    => ['bad' => ['Kotor', 'kotor'], 'ok' => 'Bersih'],
                'verification' => ['bad' => [0, '0', false], 'ok' => 1],
            ],
            'details.items.followups' => [
                'verification' => ['bad' => [0, '0', false], 'ok' => 1],
            ],
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(DetailProcessAreaCleanliness::class, 'report_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }

        /* ===================== IMPORT EXCEL ===================== */

    // Sama dengan form create
    public const SECTIONS   = ['MP', 'Cooking', 'Packing', 'Cartoning', 'Cooking Bakso', 'Pasteurisasi'];
    public const CONDITIONS = ['Bersih', 'Kotor'];
    public const ITEMS      = [
        0 => 'Kondisi Kebersihan Ruangan',
        1 => 'Kondisi Kebersihan Peralatan',
        2 => 'Kondisi Kebersihan Karyawan',
        3 => 'Suhu ruang (℃)',
    ];

    // Peta "batch upload" => uuid laporan dan detail, supaya baris sekelompok masuk induk yang sama
    private static array $importUuids = [];

    public static function getImportMapping(): array
    {
        return [
            // Laporan + detail (sama di semua baris satu inspeksi)
            'Tanggal'                       => 'date',
            'Shift'                         => 'shift',
            'Area'                          => 'section_name',
            'Jam Inspeksi'                  => 'inspection_hour',
            // Item
            'Item'                          => 'item',
            'Kondisi'                       => 'condition',
            'Suhu Actual (°C)'              => 'temperature_actual',
            'Suhu Display (°C)'             => 'temperature_display',
            'Catatan'                       => 'notes',
            'Tindakan Koreksi'              => 'corrective_action',
            'Hasil Verifikasi'              => 'verification',
            // Koreksi lanjutan (opsional)
            'Lanjutan 1 - Catatan'          => 'fu1_notes',
            'Lanjutan 1 - Tindakan Koreksi' => 'fu1_action',
            'Lanjutan 1 - Verifikasi'       => 'fu1_verification',
            'Lanjutan 2 - Catatan'          => 'fu2_notes',
            'Lanjutan 2 - Tindakan Koreksi' => 'fu2_action',
            'Lanjutan 2 - Verifikasi'       => 'fu2_verification',
        ];
    }

    public static function getImportRules(): array
    {
        return [
            // dari controller
            'import_batch' => 'required',
            'area_uuid'    => 'required',
            'created_by'   => 'required|string',

            // laporan + detail (dari Excel)
            'date'            => 'required|date',
            'shift'           => 'required|string|max:50',
            'section_name'    => ['required', Rule::in(self::SECTIONS)],
            'inspection_hour' => 'required|date_format:H:i',

            // item (dari Excel, sudah dinormalisasi di transformImportRow)
            'item'                => ['required', Rule::in(self::ITEMS)],
            'condition'           => ['nullable', Rule::in(self::CONDITIONS)],
            'temperature_actual'  => 'nullable|numeric',
            'temperature_display' => 'nullable|numeric',
            'notes'               => 'nullable|string',
            'corrective_action'   => 'nullable|string',
            'verification'        => 'required|in:0,1',
            'followups'           => 'array',
        ];
    }

    public static function getMasterDataFields(): array
    {
        $verification = ['OK', 'Tidak OK'];

        return [
            'section_name'     => ['label' => 'Area', 'options' => self::SECTIONS],
            'item'             => ['label' => 'Item', 'options' => array_values(self::ITEMS)],
            'condition'        => ['label' => 'Kondisi', 'options' => self::CONDITIONS],
            'verification'     => ['label' => 'Hasil Verifikasi', 'options' => $verification],
            'fu1_verification' => ['label' => 'Verifikasi Lanjutan 1', 'options' => $verification],
            'fu2_verification' => ['label' => 'Verifikasi Lanjutan 2', 'options' => $verification],
        ];
    }

    // Baris contoh di template: satu inspeksi dengan 4 item
    public static function getImportPrefillRows(): array
    {
        $base = [
            'date'            => now()->toDateString(),
            'shift'           => self::defaultShift(),
            'section_name'    => self::SECTIONS[0],
            'inspection_hour' => '08:30',
        ];

        $rows = [];
        foreach ([0, 1, 2] as $i) {
            $rows[] = $base + [
                'item'         => self::ITEMS[$i],
                'condition'    => 'Bersih',
                'verification' => 'OK',
            ];
        }

        $rows[] = $base + [
            'item'                => self::ITEMS[3],
            'temperature_actual'  => 4.0,
            'temperature_display' => 4.2,
            'verification'        => 'OK',
        ];

        return $rows;
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

        // Area
        if (isset($row['section_name'])) {
            $row['section_name'] = self::matchOption((string) $row['section_name'], self::SECTIONS);
        }

        // Jam inspeksi
        if (array_key_exists('inspection_hour', $row)) {
            $hour = self::toHi($row['inspection_hour']);
            if ($hour === null) {
                throw new \RuntimeException('Jam Inspeksi tidak valid.');
            }
            $row['inspection_hour'] = $hour;
        }

        // Item: cocokkan ke daftar resmi (nama suhu ruang boleh ditulis "Suhu ruang (°C)")
        $name = trim((string) ($row['item'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('Item wajib diisi.');
        }

        $idx = null;
        foreach (self::ITEMS as $i => $def) {
            if (mb_strtolower($def) === mb_strtolower($name)) {
                $idx = $i;
                break;
            }
        }
        if ($idx === null && str_starts_with(mb_strtolower($name), 'suhu ruang')) {
            $idx = 3;
        }
        if ($idx === null) {
            throw new \RuntimeException("Item \"{$name}\" tidak dikenal.");
        }
        $row['item'] = self::ITEMS[$idx];

        // Teks bebas yang bisa terbaca sebagai angka oleh Excel
        foreach (['notes', 'corrective_action'] as $key) {
            if (isset($row[$key])) {
                $row[$key] = (string) $row[$key];
            }
        }

        if ($idx === 3) {
            // Item suhu ruang: suhu actual dan display wajib, verifikasi wajib, tanpa kondisi
            foreach (['temperature_actual' => 'Suhu Actual', 'temperature_display' => 'Suhu Display'] as $key => $label) {
                $v = isset($row[$key]) ? str_replace(',', '.', trim((string) $row[$key])) : '';
                if ($v === '' || !is_numeric($v)) {
                    throw new \RuntimeException("{$label} wajib diisi dengan angka untuk item suhu ruang.");
                }
                $row[$key] = $v;
            }
            if (!isset($row['verification'])) {
                throw new \RuntimeException('Hasil Verifikasi wajib diisi untuk item suhu ruang.');
            }

            $row['condition']    = null;
            $row['verification'] = self::parseVerification($row['verification'], 'Hasil Verifikasi');
        } else {
            // Item 1-3: kondisi wajib Bersih / Kotor
            $cond = isset($row['condition']) ? self::matchOption((string) $row['condition'], self::CONDITIONS) : '';
            if (!in_array($cond, self::CONDITIONS, true)) {
                throw new \RuntimeException("Kondisi wajib diisi (Bersih atau Kotor) untuk item \"{$row['item']}\".");
            }
            $row['condition'] = $cond;

            // Verifikasi: bila kosong, OK jika Bersih, Tidak OK jika Kotor (sama dengan form)
            $row['verification'] = isset($row['verification'])
                ? self::parseVerification($row['verification'], 'Hasil Verifikasi')
                : ($cond === 'Bersih' ? 1 : 0);
        }

        // Koreksi lanjutan (maksimal 2): dibuat bila salah satu kolomnya diisi
        $followups = [];
        foreach ([1, 2] as $n) {
            $notes  = $row["fu{$n}_notes"] ?? null;
            $action = $row["fu{$n}_action"] ?? null;
            $verif  = $row["fu{$n}_verification"] ?? null;

            if ($notes === null && $action === null && $verif === null) {
                continue;
            }

            $followups[] = [
                'notes'        => $notes !== null ? (string) $notes : null,
                'action'       => $action !== null ? (string) $action : null,
                'verification' => $verif !== null ? self::parseVerification($verif, "Lanjutan {$n} - Verifikasi") : 0,
            ];
        }
        $row['followups'] = $followups;

        return $row;
    }

    public static function importRow(array $row): void
    {
        $batch     = $row['import_batch'];
        $reportKey = implode('|', [$row['date'], $row['shift'], $row['section_name']]);

        // Laporan per tanggal + shift + area, detail per jam inspeksi, dalam SATU upload
        $reportUuid = self::$importUuids[$batch]['report'][$reportKey] ??= (string) Str::uuid();
        $detailUuid = self::$importUuids[$batch]['detail'][$reportKey . '|' . $row['inspection_hour']] ??= (string) Str::uuid();

        // firstOrCreate: bila baris pertama gagal dan dibatalkan, baris berikutnya membuatnya ulang
        $report = static::firstOrCreate(
            ['uuid' => $reportUuid],
            [
                'area_uuid'    => $row['area_uuid'],
                'date'         => $row['date'],
                'shift'        => $row['shift'],
                'section_name' => $row['section_name'],
                'created_by'   => $row['created_by'],
            ]
        );

        $detail = DetailProcessAreaCleanliness::firstOrCreate(
            ['uuid' => $detailUuid],
            [
                'report_uuid'     => $report->uuid,
                'inspection_hour' => $row['inspection_hour'],
            ]
        );

        // Satu item hanya sekali per inspeksi (baris ganda tidak menggandakan item)
        if (ItemProcessAreaCleanliness::where('detail_uuid', $detail->uuid)->where('item', $row['item'])->exists()) {
            throw new \RuntimeException("Item \"{$row['item']}\" sudah ada pada jam inspeksi {$row['inspection_hour']}.");
        }

        $item = ItemProcessAreaCleanliness::create([
            'detail_uuid'         => $detail->uuid,
            'item'                => $row['item'],
            'condition'           => $row['condition'] ?? null,
            'temperature_actual'  => $row['temperature_actual'] ?? null,
            'temperature_display' => $row['temperature_display'] ?? null,
            'notes'               => $row['notes'] ?? null,
            'corrective_action'   => $row['corrective_action'] ?? null,
            'verification'        => $row['verification'],
        ]);

        foreach ($row['followups'] ?? [] as $followup) {
            ItemFollowup::create([
                'item_id'      => $item->id,
                'notes'        => $followup['notes'],
                'action'       => $followup['action'],
                'verification' => $followup['verification'],
            ]);
        }
    }
}