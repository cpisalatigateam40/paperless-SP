<?php

namespace App\Imports;

use App\Models\{
    ReportMdProduct,
    DetailMdProduct,
    PositionMdProduct,
    Product
};
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MdProductImport implements ToCollection
{
    protected array $reports = [];

    public function collection(Collection $rows)
    {
        foreach ($rows->skip(1) as $row) {

            if (empty($row[6])) continue;

            /* ================= HEADER ================= */
            $tanggal = $this->parseDate($row[1]);
            if (!$tanggal) continue;

            $shift = trim($row[2] ?? '');
            $group = trim($row[5] ?? '');

            $shiftFinal = $shift && $group
                ? "{$shift}-{$group}"
                : $shift;

            $qc = trim($row[4] ?? auth()->user()->name);

            $headerKey = implode('|', [
                $tanggal,
                $shiftFinal,
                $qc,
                auth()->user()->area_uuid,
            ]);

            if (!isset($this->reports[$headerKey])) {
                $this->reports[$headerKey] = ReportMdProduct::firstOrCreate(
                    [
                        'date'       => $tanggal,
                        'shift'      => $shiftFinal, // ✅ SUDAH DIGABUNG
                        'created_by' => $qc,
                        'area_uuid'  => auth()->user()->area_uuid,
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                    ]
                );
            }

            $report = $this->reports[$headerKey];

            /* ================= PRODUCT ================= */
            $productName = trim(explode('-', $row[6])[0]);
            $product = Product::where('product_name', $productName)->first();
            if (!$product) continue;

            /* ================= DETAIL ================= */
            $detail = DetailMdProduct::firstOrCreate(
                [
                    'report_uuid'     => $report->uuid,
                    'product_uuid'    => $product->uuid,
                    'production_code' => trim($row[8] ?? ''),
                    'time'            => $this->parseTime($row[3] ?? null),
                ],
                [
                    'uuid'             => (string) Str::uuid(),
                    'nett_weight'      => $row[7] ?? null,
                    'best_before'      => $this->parseDate($row[9] ?? null),
                    'program_number'   => $row[10] ?? null,
                    'process_type'     => $row[11] ?? null,
                    'corrective_action'=> $row[24] ?? null,
                    'verification'     => $this->symbolBool($row[25] ?? null),
                ]
            );

            /* ================= POSITIONS ================= */
            $map = [
                'fe_1_5mm'   => [12,13,14,15],
                'non_fe_2mm' => [16,17,18,19],
                'sus_2_5mm'  => [20,21,22,23],
            ];

            foreach ($map as $specimen => $cols) {
                foreach ($cols as $i => $col) {

                    $status = $this->symbolBool($row[$col] ?? null);
                    if ($status === null) continue;

                    $positionKey = [
                        'detail_uuid' => $detail->uuid,
                        'specimen'    => $specimen,
                        'position'    => ['d','t','b','dl'][$i],
                    ];

                    $position = PositionMdProduct::where($positionKey)->first();

                    if ($position) {
                        $position->update([
                            'status' => $status,
                        ]);
                    } else {
                        PositionMdProduct::create([
                            'uuid'        => (string) Str::uuid(),
                            ...$positionKey,
                            'status'      => $status,
                        ]);
                    }
                }
            }
        }
    }

    /* ================= HELPERS ================= */

    private function symbolBool($v): ?bool
    {
        return match (strtoupper(trim((string)$v))) {
            'OK','✓','1' => true,
            'TIDAK OK','NG','X','0' => false,
            default => null,
        };
    }

    private function parseDate($v): ?string
    {
        if (!$v) return null;
        if (is_numeric($v)) {
            return Carbon::instance(
                ExcelDate::excelToDateTimeObject($v)
            )->format('Y-m-d');
        }
        try {
            return Carbon::parse($v)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseTime($v): ?string
    {
        if (!$v) return null;
        if (is_numeric($v)) {
            return Carbon::instance(
                ExcelDate::excelToDateTimeObject($v)
            )->format('H:i:s');
        }
        try {
            return Carbon::parse($v)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}