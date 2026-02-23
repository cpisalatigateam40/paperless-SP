<?php

namespace App\Imports;

use App\Models\{
    ReportRmArrival,
    DetailRmArrival,
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

class RmArrivalImport implements ToCollection, WithHeadingRow
{
    protected string $sectionUuid;

    /** @var array<string, ReportRmArrival> */
    protected array $reports = [];

    public function __construct(string $sectionUuid)
    {
        $this->sectionUuid = $sectionUuid;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            /* ================= VALIDASI WAJIB ================= */
            if (
                empty($row['bahan']) ||
                empty($row['tanggal']) ||
                empty($row['shift']) ||
                empty($row['group']) ||
                empty($row['qc'])
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

            /* ================= HEADER KEY ================= */
            $headerKey = implode('|', [
                $tanggal,
                $shiftFinal,
                $qc,
                auth()->user()->area_uuid,
                $this->sectionUuid,
            ]);

            /* ================= REPORT ================= */
            if (!isset($this->reports[$headerKey])) {
                $this->reports[$headerKey] =
                    ReportRmArrival::withoutGlobalScopes()
                        ->firstOrCreate(
                            [
                                'date'         => $tanggal,
                                'shift'        => $shiftFinal,
                                'created_by'   => $qc,
                                'area_uuid'    => auth()->user()->area_uuid,
                                'section_uuid' => $this->sectionUuid,
                            ],
                            [
                                'uuid' => Str::uuid(),
                            ]
                        );
            }

            $report = $this->reports[$headerKey];

            /* ================= MATERIAL ================= */
            $raw = RawMaterial::where('material_name', trim($row['bahan']))->first();
            $premix = Premix::where('name', trim($row['bahan']))->first();

            if (!$raw && !$premix) {
                continue;
            }

            $materialType = $raw ? 'raw' : 'premix';
            $materialUuid = $raw ? $raw->uuid : $premix->uuid;

            /* ================= NORMALISASI ================= */
            $productionCode = trim($row['kode_prod'] ?? '');
            $time = $this->parseTime($row['time'] ?? null);

            /* ================= DETAIL UPSERT ================= */
            DetailRmArrival::updateOrCreate(
                [
                    'report_uuid'   => $report->uuid,
                    'material_type' => $materialType,
                    $raw ? 'raw_material_uuid' : 'material_uuid' => $materialUuid,
                    'production_code' => $productionCode ?: null,
                    'time'            => $time,
                ],
                [
                    'uuid'              => Str::uuid(),
                    'material_uuid'     => $premix?->uuid,
                    'raw_material_uuid' => $raw?->uuid,

                    'supplier'          => $row['supplier'] ?? null,
                    'rm_condition'      => $row['kondisi'] ?? null,
                    'temperature'       => $row['suhu_c'] ?? null,

                    'packaging_condition' => $this->symbol($row['kemasan'] ?? null),
                    'sensory_appearance'  => $this->symbol($row['kenampakan'] ?? null),
                    'sensory_aroma'       => $this->symbol($row['aroma'] ?? null),
                    'sensory_color'       => $this->symbol($row['warna'] ?? null),
                    'contamination'       => $this->symbol($row['kontaminasi'] ?? null),

                    'problem'           => $row['problem'] ?? null,
                    'corrective_action' => $row['tindakan_koreksi'] ?? null,
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
        if ($value === null || $value === '') return null;

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
