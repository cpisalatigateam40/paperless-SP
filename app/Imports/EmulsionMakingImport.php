<?php

namespace App\Imports;

use App\Models\{
    ReportEmulsionMaking,
    HeaderEmulsionMaking,
    DetailEmulsionMaking,
    AgingEmulsionMaking,
    RawMaterial,
    Premix
};
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\{
    ToCollection,
    WithHeadingRow
};
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EmulsionMakingImport implements ToCollection, WithHeadingRow
{
    protected array $reports = [];
    protected array $headers = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            /* ================= VALIDASI WAJIB ================= */
            if (
                empty($row['tanggal']) ||
                empty($row['shift']) ||
                empty($row['group']) ||
                empty($row['qc']) ||
                empty($row['jenis_emulsi']) ||
                empty($row['kode_prod']) ||
                empty($row['bahan'])
            ) {
                continue;
            }

            /* ================= DATE ================= */
            $tanggal = $this->parseDate($row['tanggal']);
            if (!$tanggal) continue;

            /* ================= SHIFT ================= */
            $shiftFinal = trim($row['shift']) . '-' . strtoupper(trim($row['group']));

            /* ================= QC ================= */
            $qc = trim($row['qc']);

            /* ================= REPORT KEY ================= */
            $reportKey = implode('|', [
                $tanggal,
                $shiftFinal,
                $qc,
                auth()->user()->area_uuid,
            ]);

            /* ================= REPORT ================= */
            if (!isset($this->reports[$reportKey])) {
                $this->reports[$reportKey] =
                    ReportEmulsionMaking::withoutGlobalScopes()
                        ->firstOrCreate(
                            [
                                'date'       => $tanggal,
                                'shift'      => $shiftFinal,
                                'created_by' => $qc,
                                'area_uuid'  => auth()->user()->area_uuid,
                            ],
                            [
                                'uuid' => Str::uuid(),
                            ]
                        );
            }

            $report = $this->reports[$reportKey];

            /* ================= HEADER KEY ================= */
            $headerKey = implode('|', [
                $report->uuid,
                trim($row['jenis_emulsi']),
                trim($row['kode_prod']),
            ]);

            /* ================= HEADER ================= */
            if (!isset($this->headers[$headerKey])) {
                $this->headers[$headerKey] =
                    HeaderEmulsionMaking::firstOrCreate(
                        [
                            'report_uuid'     => $report->uuid,
                            'emulsion_type'   => trim($row['jenis_emulsi']),
                            'production_code' => trim($row['kode_prod']),
                        ],
                        [
                            'uuid' => Str::uuid(),
                        ]
                    );
            }

            $header = $this->headers[$headerKey];

            /* ================= MATERIAL ================= */
            $raw = RawMaterial::where('material_name', trim($row['bahan']))->first();
            $premix = Premix::where('name', trim($row['bahan']))->first();

            if (!$raw && !$premix) {
                continue;
            }

            $materialType = $raw ? 'raw' : 'premix';
            $materialUuid = $raw ? $raw->uuid : $premix->uuid;

            /* ================= DETAIL (UPSERT) ================= */
            DetailEmulsionMaking::updateOrCreate(
                [
                    'header_uuid'   => $header->uuid,
                    'material_type' => $materialType,
                    $raw ? 'raw_material_uuid' : 'material_uuid' => $materialUuid,
                ],
                [
                    'uuid'              => Str::uuid(),
                    'raw_material_uuid' => $raw?->uuid,
                    'material_uuid'     => $premix?->uuid,
                    'weight'            => $row['berat'] ?? null,
                    'temperature'       => $row['suhu'] ?? null,
                    'conformity'        => $this->symbol($row['kesesuaian'] ?? null),
                ]
            );

            /* ================= AGING (1 HEADER = 1 AGING) ================= */
            AgingEmulsionMaking::updateOrCreate(
                [
                    'header_uuid' => $header->uuid,
                ],
                [
                    'uuid'            => Str::uuid(),
                    'start_aging'     => $this->parseTime($row['awal_proses'] ?? null),
                    'finish_aging'    => $this->parseTime($row['akhir_proses'] ?? null),
                    'sensory_color'   => $row['warna_emulsi'] ?? null,
                    'sensory_texture' => $row['tekstur_emulsi'] ?? null,
                    'temp_after'      => $row['suhu_emulsi'] ?? null,
                    'emulsion_result' => $row['hasil_emulsi'] ?? null,
                ]
            );
        }
    }

    /* ================= HELPERS ================= */

    private function symbol($value): ?string
    {
        $v = trim((string) $value);
        return in_array($v, ['✓', 'x'], true) ? $v : null;
    }

    private function parseTime($value): ?string
    {
        if (!$value) return null;

        if (is_numeric($value)) {
            return Carbon::instance(
                ExcelDate::excelToDateTimeObject($value)
            )->format('H:i:s');
        }

        try {
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDate($value): ?string
    {
        if (!$value) return null;

        if (is_numeric($value)) {
            return Carbon::instance(
                ExcelDate::excelToDateTimeObject($value)
            )->format('Y-m-d');
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
