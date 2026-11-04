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

class ReportStorageRmCleanliness extends Model implements Auditable, ImportableModel, CustomImportRow
{
    use HasFactory;
    use HasAudit {
        copyToAudit as copyHeaderToAudit;
    }
    use \OwenIt\Auditing\Auditable;
    use ParsesExcelValues;

    protected $table = 'report_storage_rm_cleanliness';
    protected $fillable = [
        'uuid',
        'area_uuid',
        'date',
        'shift',
        'room_name',
        'created_by',
        'known_by',
        'approved_by',
        'is_audit',
        'source_uuid'
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
        // nilai tidak OK => nilai OK pasangannya (berbeda per item)
        $conditionMap = [
            'Tidak tertata rapi'            => 'Tertata rapi',
            'Penempatan tidak sesuai'       => 'Sesuai tagging dan jenis alergen',
            'Tidak bersih / ada kontaminan' => 'Bersih dan bebas kontaminan',
        ];

        $conditionBad = fn ($row) => isset($conditionMap[$row->condition]);

        return [
            'details.items' => [
                'condition' => [
                    'when' => $conditionBad,
                    'ok'   => fn ($row) => $conditionMap[$row->condition],
                ],
                // catatan hanya diganti untuk item yang kondisinya tidak OK (item 1-3)
                'notes' => [
                    'when' => $conditionBad,
                    'ok'   => json_encode(['Sesuai']),
                ],
                'verification' => ['bad' => [0, '0', false], 'ok' => 1],
            ],
            'details.items.followups' => [
                'verification' => ['bad' => [0, '0', false], 'ok' => 1],
            ],
        ];
    }

    public function details()
    {
        return $this->hasMany(DetailStorageRmCleanliness::class, 'report_uuid', 'uuid');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UserAreaScope);
    }

        /* ===================== IMPORT EXCEL ===================== */

    public const ROOMS = ['Seasoning', 'Chillroom'];

    // Sama dengan form create: nama item (tersimpan), kondisi OK, kondisi tidak OK, jenis daftar catatan
    public const ITEMS = [
        0 => ['name' => 'Kondisi dan penempatan barang', 'ok' => 'Tertata rapi', 'bad' => 'Tidak tertata rapi', 'notes' => 'storage'],
        1 => ['name' => 'Pelabelan', 'ok' => 'Sesuai tagging dan jenis alergen', 'bad' => 'Penempatan tidak sesuai', 'notes' => 'storage'],
        2 => ['name' => 'Kebersihan Ruangan', 'ok' => 'Bersih dan bebas kontaminan', 'bad' => 'Tidak bersih / ada kontaminan', 'notes' => 'clean'],
        3 => ['name' => 'Suhu ruang (℃) / RH (%)'],
    ];

    public const NOTES_STORAGE = [
        'Sesuai',
        'Penataan bahan tidak rapi',
        'Penempatan bahan tidak sesuai dengan labelnya',
        'Tidak ada label/tagging di tempat penyimpanan',
    ];

    public const NOTES_CLEAN = [
        'Sesuai',
        'Rak penyimpanan bahan kotor',
        'Langit-langit kotor',
        'Pintu kotor',
        'Dinding kotor',
        'Curving kotor',
        'Curtain kotor',
        'Lantai kotor/basah',
        'Pallet kotor',
        'Lampu + cover kotor',
        'Exhaust fan kotor',
        'Evaporator kotor',
        'Temuan pest di area produksi',
        'Temuan pest di dalam bahan',
    ];

    // Sama dengan koreksiMap di JavaScript form create
    public const CORRECTIONS = [
        'Sesuai'                                       => '-',
        'Penataan bahan tidak rapi'                    => 'Penataan bahan dengan rapi',
        'Penempatan bahan tidak sesuai dengan labelnya' => 'Penataan bahan sesuai dengan labelnya',
        'Tidak ada label/tagging di tempat penyimpanan' => 'Labelling/tagging sesuai tempat penyimpanan',
        'Rak penyimpanan bahan kotor'                  => 'Cleaning area/peralatan yang kotor',
        'Langit-langit kotor'                          => 'Cleaning area/peralatan yang kotor',
        'Pintu kotor'                                  => 'Cleaning area/peralatan yang kotor',
        'Dinding kotor'                                => 'Cleaning area/peralatan yang kotor',
        'Curving kotor'                                => 'Cleaning area/peralatan yang kotor',
        'Curtain kotor'                                => 'Cleaning area/peralatan yang kotor',
        'Lantai kotor/basah'                           => 'Cleaning area/peralatan yang kotor',
        'Pallet kotor'                                 => 'Cleaning area/peralatan yang kotor',
        'Lampu + cover kotor'                          => 'Cleaning area/peralatan yang kotor',
        'Exhaust fan kotor'                            => 'Cleaning area/peralatan yang kotor',
        'Evaporator kotor'                             => 'Cleaning area/peralatan yang kotor',
        'Temuan pest di area produksi'                 => 'Inspeksi pest',
        'Temuan pest di dalam bahan'                   => 'Inspeksi pest',
    ];

    // Peta "batch upload" => uuid laporan dan detail, supaya baris sekelompok masuk induk yang sama
    private static array $importUuids = [];

    private static function allowedNotes(int $idx): array
    {
        return self::ITEMS[$idx]['notes'] === 'clean' ? self::NOTES_CLEAN : self::NOTES_STORAGE;
    }

    // "Pintu kotor; Dinding kotor" -> ['Pintu kotor', 'Dinding kotor']
    private static function splitList($v): array
    {
        $parts = preg_split('/\s*[;,\n|]\s*/', trim((string) $v)) ?: [];
        return array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));
    }

    // OK / Tidak OK / ✓ / x / 1 / 0 -> 1 atau 0
    private static function parseVerification($v, string $label): int
    {
        try {
            return self::toBool($v, $label) ? 1 : 0;
        } catch (\RuntimeException $e) {
            throw new \RuntimeException("Nilai \"{$v}\" pada kolom {$label} tidak valid, gunakan OK atau Tidak OK.");
        }
    }

    public static function getImportMapping(): array
    {
        return [
            // Laporan + detail (sama di semua baris satu inspeksi)
            'Tanggal'                        => 'date',
            'Shift'                          => 'shift',
            'Area (Room Name)'               => 'room_name',
            'Jam Inspeksi'                   => 'inspection_hour',
            // Item
            'Item'                           => 'item',
            'Kondisi'                        => 'condition',
            'Suhu (°C)'                      => 'temperature',
            'Catatan'                        => 'notes',
            'Tindakan Koreksi'               => 'corrective_action',
            'Verifikasi'                     => 'verification',
            // Koreksi lanjutan (opsional)
            'Lanjutan 1 - Catatan'           => 'fu1_notes',
            'Lanjutan 1 - Tindakan Koreksi'  => 'fu1_corrective_action',
            'Lanjutan 1 - Verifikasi'        => 'fu1_verification',
            'Lanjutan 2 - Catatan'           => 'fu2_notes',
            'Lanjutan 2 - Tindakan Koreksi'  => 'fu2_corrective_action',
            'Lanjutan 2 - Verifikasi'        => 'fu2_verification',
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
            'room_name'       => ['required', Rule::in(self::ROOMS)],
            'inspection_hour' => 'required|date_format:H:i',

            // item (dari Excel, sudah dinormalisasi di transformImportRow)
            'item'              => ['required', Rule::in(array_column(self::ITEMS, 'name'))],
            'condition'         => 'required|string',
            'notes'             => 'nullable',
            'corrective_action' => 'nullable|string',
            'verification'      => 'required|in:0,1',
            'followups'         => 'array',
        ];
    }

    public static function getMasterDataFields(): array
    {
        $conditions = [];
        foreach ([0, 1, 2] as $i) {
            $conditions[] = self::ITEMS[$i]['ok'];
            $conditions[] = self::ITEMS[$i]['bad'];
        }
        $verification = ['OK', 'Tidak OK'];

        return [
            'room_name'        => ['label' => 'Area', 'options' => self::ROOMS],
            'item'             => ['label' => 'Item', 'options' => array_column(self::ITEMS, 'name')],
            'condition'        => ['label' => 'Kondisi', 'options' => $conditions],
            'verification'     => ['label' => 'Verifikasi', 'options' => $verification],
            'fu1_verification' => ['label' => 'Verifikasi Lanjutan 1', 'options' => $verification],
            'fu2_verification' => ['label' => 'Verifikasi Lanjutan 2', 'options' => $verification],
            // Referensi saja (tanpa dropdown): nilai yang boleh diisi di kolom Catatan, dipisah titik koma
            'ref_notes_storage' => ['label' => 'Referensi Catatan Item 1 dan 2', 'options' => self::NOTES_STORAGE],
            'ref_notes_clean'   => ['label' => 'Referensi Catatan Item 3', 'options' => self::NOTES_CLEAN],
        ];
    }

    // Baris contoh di template: satu inspeksi dengan 4 item, semuanya OK
    public static function getImportPrefillRows(): array
    {
        $base = [
            'date'            => now()->toDateString(),
            'shift'           => self::defaultShift(),
            'room_name'       => self::ROOMS[0],
            'inspection_hour' => '08:30',
        ];

        $rows = [];
        foreach ([0, 1, 2] as $i) {
            $rows[] = $base + [
                'item'              => self::ITEMS[$i]['name'],
                'condition'         => self::ITEMS[$i]['ok'],
                'notes'             => 'Sesuai',
                'corrective_action' => '-',
                'verification'      => 'OK',
            ];
        }

        $rows[] = $base + [
            'item'              => self::ITEMS[3]['name'],
            'temperature'       => 4.0,
            'corrective_action' => '-',
            'verification'      => 'OK',
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
        if (isset($row['room_name'])) {
            $row['room_name'] = self::matchOption((string) $row['room_name'], self::ROOMS);
        }

        // Jam inspeksi
        if (array_key_exists('inspection_hour', $row)) {
            $hour = self::toHi($row['inspection_hour']);
            if ($hour === null) {
                throw new \RuntimeException('Jam Inspeksi tidak valid.');
            }
            $row['inspection_hour'] = $hour;
        }

        // Item: cocokkan ke daftar resmi
        $name = trim((string) ($row['item'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('Item wajib diisi.');
        }

        $idx = null;
        foreach (self::ITEMS as $i => $def) {
            if (mb_strtolower($def['name']) === mb_strtolower($name)) {
                $idx = $i;
                break;
            }
        }
        if ($idx === null) {
            throw new \RuntimeException("Item \"{$name}\" tidak dikenal.");
        }

        $def = self::ITEMS[$idx];
        $row['item'] = $def['name'];

        if ($idx === 3) {
            // Item suhu ruang: kondisi = "Suhu: X °C", catatan teks bebas, verifikasi wajib
            $temp = isset($row['temperature']) ? str_replace(',', '.', trim((string) $row['temperature'])) : '';
            if ($temp === '' || !is_numeric($temp)) {
                throw new \RuntimeException('Suhu (°C) wajib diisi dengan angka untuk item suhu ruang.');
            }
            if (!isset($row['verification'])) {
                throw new \RuntimeException('Verifikasi wajib diisi untuk item suhu ruang.');
            }

            $row['condition']    = 'Suhu: ' . $temp . ' °C';
            $row['notes']        = isset($row['notes']) ? (string) $row['notes'] : null;
            $row['verification'] = self::parseVerification($row['verification'], 'Verifikasi');
        } else {
            // Item 1-3: kondisi harus salah satu dari dua pilihan item itu
            $cond = isset($row['condition']) ? trim((string) $row['condition']) : '';
            if ($cond === '') {
                throw new \RuntimeException("Kondisi wajib diisi untuk item \"{$def['name']}\".");
            }

            $cond = self::matchOption($cond, [$def['ok'], $def['bad']]);
            if (!in_array($cond, [$def['ok'], $def['bad']], true)) {
                throw new \RuntimeException("Kondisi \"{$cond}\" tidak sesuai untuk item \"{$def['name']}\".");
            }
            $isOk = $cond === $def['ok'];
            $row['condition'] = $cond;

            // Catatan: daftar dipisah titik koma, tiap nilai harus ada di daftar item ini
            $allowed = self::allowedNotes($idx);
            $notes   = [];
            foreach (self::splitList($row['notes'] ?? '') as $n) {
                $match = self::matchOption($n, $allowed);
                if (!in_array($match, $allowed, true)) {
                    throw new \RuntimeException("Catatan \"{$n}\" tidak dikenal untuk item \"{$def['name']}\".");
                }
                $notes[] = $match;
            }
            $notes = array_values(array_unique($notes));
            if (!$notes && $isOk) {
                $notes = ['Sesuai'];
            }
            $row['notes'] = $notes ?: null;

            // Tindakan koreksi: bila kosong, turunkan dari catatan (sama dengan form)
            $corrective = trim((string) ($row['corrective_action'] ?? ''));
            if ($corrective === '') {
                $derived    = array_unique(array_filter(array_map(fn ($n) => self::CORRECTIONS[$n] ?? null, $notes)));
                $corrective = $derived ? implode(', ', $derived) : ($isOk ? '-' : '');
            }
            $row['corrective_action'] = $corrective !== '' ? $corrective : null;

            // Verifikasi: bila kosong, OK jika kondisi OK, selain itu Tidak OK
            $row['verification'] = isset($row['verification'])
                ? self::parseVerification($row['verification'], 'Verifikasi')
                : ($isOk ? 1 : 0);
        }

        // Koreksi lanjutan (maksimal 2): dibuat bila salah satu kolomnya diisi
        $followups = [];
        foreach ([1, 2] as $n) {
            $notes      = $row["fu{$n}_notes"] ?? null;
            $corrective = $row["fu{$n}_corrective_action"] ?? null;
            $verif      = $row["fu{$n}_verification"] ?? null;

            if ($notes === null && $corrective === null && $verif === null) {
                continue;
            }

            $followups[] = [
                'notes'             => $notes !== null ? (string) $notes : null,
                'corrective_action' => $corrective !== null ? (string) $corrective : null,
                'verification'      => $verif !== null ? self::parseVerification($verif, "Lanjutan {$n} - Verifikasi") : 0,
            ];
        }
        $row['followups'] = $followups;

        return $row;
    }

    public static function importRow(array $row): void
    {
        $batch     = $row['import_batch'];
        $reportKey = implode('|', [$row['date'], $row['shift'], $row['room_name']]);

        // Laporan per tanggal + shift + area, detail per jam inspeksi, dalam SATU upload
        $reportUuid = self::$importUuids[$batch]['report'][$reportKey] ??= (string) Str::uuid();
        $detailUuid = self::$importUuids[$batch]['detail'][$reportKey . '|' . $row['inspection_hour']] ??= (string) Str::uuid();

        // firstOrCreate: bila baris pertama gagal dan dibatalkan, baris berikutnya membuatnya ulang
        $report = static::firstOrCreate(
            ['uuid' => $reportUuid],
            [
                'area_uuid'  => $row['area_uuid'],
                'date'       => $row['date'],
                'shift'      => $row['shift'],
                'room_name'  => $row['room_name'],
                'created_by' => $row['created_by'],
            ]
        );

        $detail = DetailStorageRmCleanliness::firstOrCreate(
            ['uuid' => $detailUuid],
            [
                'report_uuid'     => $report->uuid,
                'inspection_hour' => $row['inspection_hour'],
            ]
        );

        // Satu item hanya sekali per inspeksi (upload ganda tidak menggandakan item)
        if (ItemStorageRmCleanliness::where('detail_uuid', $detail->uuid)->where('item', $row['item'])->exists()) {
            throw new \RuntimeException("Item \"{$row['item']}\" sudah ada pada jam inspeksi {$row['inspection_hour']}.");
        }

        $notes = $row['notes'] ?? null;

        $item = ItemStorageRmCleanliness::create([
            'detail_uuid'       => $detail->uuid,
            'item'              => $row['item'],
            'condition'         => $row['condition'],
            'notes'             => is_array($notes) ? json_encode($notes) : $notes,
            'corrective_action' => $row['corrective_action'] ?? null,
            'verification'      => $row['verification'],
        ]);

        foreach ($row['followups'] ?? [] as $followup) {
            FollowupCleanlinessStorage::create([
                'item_storage_rm_cleanliness_id' => $item->id,
                'notes'                          => $followup['notes'],
                'corrective_action'              => $followup['corrective_action'],
                'verification'                   => $followup['verification'],
            ]);
        }
    }
}