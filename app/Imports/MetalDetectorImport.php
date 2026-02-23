<?php

namespace App\Imports;

use App\Models\{
    ReportMetalDetector,
    DetailMetalDetector,
    Product
};
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\{
    ToCollection,
    WithHeadingRow
};
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MetalDetectorImport implements ToCollection, WithHeadingRow
{
    protected array $reports = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            /* ================= VALIDASI WAJIB ================= */
            if (
                empty($row['nama_produk']) ||
                empty($row['tanggal']) ||
                empty($row['shift']) ||
                empty($row['qc']) ||
                empty($row['group'])
            ) {
                continue;
            }

            /* ================= DATE ================= */
            $tanggal = $this->parseDate($row['tanggal']);
            if (!$tanggal) continue;

            /* ================= SHIFT ================= */
            $shiftFinal = trim($row['shift']) . '-' . strtoupper(trim($row['group']));

            /* ================= QC (WAJIB, TIDAK BOLEH FALLBACK) ================= */
            $qc = trim($row['qc']);

            /* ================= HEADER KEY ================= */
            $headerKey = implode('|', [
                $tanggal,
                $shiftFinal,
                $qc,
                auth()->user()->area_uuid,
            ]);

            /* ================= HEADER ================= */
            if (!isset($this->reports[$headerKey])) {
                $this->reports[$headerKey] =
                    ReportMetalDetector::withoutGlobalScopes()
                        ->firstOrCreate(
                            [
                                'date'       => $tanggal,
                                'shift'      => $shiftFinal,
                                'created_by' => $qc,
                                'area_uuid'  => auth()->user()->area_uuid,
                            ],
                            [
                                'uuid'         => Str::uuid(),
                                'section_uuid' => null,
                            ]
                        );
            }

            $report = $this->reports[$headerKey];

            /* ================= PRODUCT ================= */
            $product = Product::where('product_name', trim($row['nama_produk']))->first();
            if (!$product) continue;

            /* ================= DETAIL UNIQUE KEY ================= */
            $hour = $this->parseTime($row['time'] ?? null);
            $productionCode = trim($row['kode_prod'] ?? '');

            /* ================= DETAIL ================= */
            $detail = DetailMetalDetector::where('report_uuid', $report->uuid)
                ->where('product_uuid', $product->uuid)
                ->where('production_code', $productionCode)
                ->first();

            $payload = [
                'hour'                => $hour,
                'result_fe'           => $this->symbol($row['speci_fe_15_mm'] ?? null),
                'result_non_fe'       => $this->symbol($row['speci_non_fe_20_mm'] ?? null),
                'result_sus316'       => $this->symbol($row['speci_sus_25_mm'] ?? null),
                'verif_loma'          => $row['hasil_verifikasi'] ?? null,
                'nonconformity'       => $row['ketidaksesuaian'] ?? null,
                'corrective_action'   => $row['tindakan_koreksi'] ?? null,
                'verif_after_correct' => $row['hasil_verifikasi_setelah_tindakan_perbaikan'] ?? null,
            ];

            if ($detail) {
                $detail->update($payload);
            } else {
                DetailMetalDetector::create(array_merge($payload, [
                    'uuid'            => Str::uuid(),
                    'report_uuid'     => $report->uuid,
                    'product_uuid'    => $product->uuid,
                    'production_code' => $productionCode,
                ]));
            }
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
