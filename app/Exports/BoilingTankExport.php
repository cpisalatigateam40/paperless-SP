<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class BoilingTankExport implements WithEvents, WithTitle
{
    private int $maxChecks;

    public function __construct(
        private $reports,
        private string $periodLabel,
        private Collection $standards,
    ) {
        $this->maxChecks = max(
            $this->reports->flatMap(fn ($r) => $r->details)
                ->max(fn ($d) => $d->checks->count()) ?? 0,
            3
        );
    }

    public function title(): string
    {
        return 'Boiling Tank';
    }

    private function standardFor($report): ?\App\Models\MasterBoilingTankStandard
    {
        return $this->standards->get($report->area_uuid . '|' . $report->product_uuid);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $fixedHeaders = [
                    'No', 'Tanggal', 'Shift', 'Nama Produk', 'Kode Produk', 'Gramasi (gr)',
                    'Line Boiling Tank', 'Waktu Proses', 'Kode Produksi', 'Start', 'End',
                    'Suhu Adonan (°C)',
                    'Suhu Tangki I (°C)', 'Std Suhu Tangki I',
                    'Suhu Tangki II (°C)', 'Std Suhu Tangki II',
                ];
                $tailHeaders = ['Bentuk', 'Warna', 'Aroma', 'Rasa', 'Tekstur'];

                // Grup pemeriksaan (Berat Mentah, Actual Core Temp, Berat Matang, Suhu After Cooling)
                // + 1 kolom "Std" setelah tiap grup Berat Mentah / Berat Matang
                $groupLabels = [
                    'berat_mentah' => 'Berat Mentah (gr)',
                    'actual_core_temp' => 'Actual Core Temp (°C)',
                    'berat_matang' => 'Berat Matang (gr)',
                    'suhu_after_cooling' => 'Suhu After Cooling (°C)',
                ];
                $stdAfterGroup = ['berat_mentah', 'berat_matang']; // grup yang dapat kolom Std tambahan

                $totalCols = count($fixedHeaders)
                    + (count($groupLabels) * $this->maxChecks)
                    + (count($stdAfterGroup)) // 1 kolom Std per grup yang butuh
                    + count($tailHeaders);
                $lastCol = Coordinate::stringFromColumnIndex($totalCols);

                // ── Title ────────────────────────────────────────────────
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Verifikasi Proses Pemasakan di Boiling Tank');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Header row 4-5 ───────────────────────────────────────
                $col = 1;
                $colMap = []; // simpan posisi kolom penting untuk dipakai lagi di data row

                foreach ($fixedHeaders as $label) {
                    $letter = Coordinate::stringFromColumnIndex($col);
                    $sheet->mergeCells("{$letter}4:{$letter}5");
                    $sheet->setCellValue("{$letter}4", $label);
                    $colMap['fixed_' . $label] = $col;
                    $col++;
                }

                foreach ($groupLabels as $key => $groupLabel) {
                    $startLetter = Coordinate::stringFromColumnIndex($col);
                    $endLetter = Coordinate::stringFromColumnIndex($col + $this->maxChecks - 1);
                    $sheet->mergeCells("{$startLetter}4:{$endLetter}4");
                    $sheet->setCellValue("{$startLetter}4", $groupLabel);

                    $colMap['group_' . $key] = $col;

                    for ($i = 1; $i <= $this->maxChecks; $i++) {
                        $letter = Coordinate::stringFromColumnIndex($col);
                        $sheet->setCellValue("{$letter}5", $i);
                        $col++;
                    }

                    if (in_array($key, $stdAfterGroup, true)) {
                        $letter = Coordinate::stringFromColumnIndex($col);
                        $sheet->mergeCells("{$letter}4:{$letter}5");
                        $sheet->setCellValue("{$letter}4", 'Std');
                        $colMap['std_' . $key] = $col;
                        $col++;
                    }
                }

                foreach ($tailHeaders as $label) {
                    $letter = Coordinate::stringFromColumnIndex($col);
                    $sheet->mergeCells("{$letter}4:{$letter}5");
                    $sheet->setCellValue("{$letter}4", $label);
                    $col++;
                }

                $sheet->getStyle("A4:{$lastCol}5")->getFont()->setBold(true);
                $sheet->getStyle("A4:{$lastCol}5")->getAlignment()
                    ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                $sheet->getStyle("A4:{$lastCol}5")->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                // ── Data (mulai row 6) ───────────────────────────────────
                $dataRow = 6;
                $no = 1;

                foreach ($this->reports as $report) {
                    $standard = $this->standardFor($report);

                    foreach ($report->details as $detail) {
                        $col = 1;
                        $set = function ($value) use ($sheet, &$col, $dataRow) {
                            $letter = Coordinate::stringFromColumnIndex($col);
                            $sheet->setCellValue("{$letter}{$dataRow}", $value);
                            $col++;
                        };

                        $set($no);
                        $set(Carbon::parse($report->date)->format('d/m/Y'));
                        $set($report->shift);
                        $set($report->product->product_name ?? '-');
                        $set($report->product_code ?? '-');
                        $set($report->gramasi ?? '-');
                        $set($report->line_boiling_tank ?? '-');
                        $set(($report->waktu_proses_start ?? '--:--') . ' - ' . ($report->waktu_proses_end ?? '--:--'));
                        $set($detail->kode_produksi ?? '-');
                        $set($detail->start ?? '-');
                        $set($detail->end ?? '-');
                        $set($detail->suhu_adonan ?? '-');

                        $set($detail->aktual_suhu_tangki_1 ?? '-');
                        $set($standard?->suhu_tangki_1_label ?? '-');

                        $set($detail->aktual_suhu_tangki_2 ?? '-');
                        $set($standard?->suhu_tangki_2_label ?? '-');

                        for ($i = 0; $i < $this->maxChecks; $i++) {
                            $set($detail->checks->get($i)?->berat_mentah ?? '-');
                        }
                        $set($standard?->berat_mentah_label ?? '-');

                        for ($i = 0; $i < $this->maxChecks; $i++) {
                            $set($detail->checks->get($i)?->actual_core_temp ?? '-');
                        }
                        $set($standard?->actual_core_temp_label ?? '-');

                        for ($i = 0; $i < $this->maxChecks; $i++) {
                            $set($detail->checks->get($i)?->berat_matang ?? '-');
                        }
                        $set($standard?->berat_matang_label ?? '-');

                        for ($i = 0; $i < $this->maxChecks; $i++) {
                            $set($detail->checks->get($i)?->suhu_after_cooling ?? '-');
                        }

                        $set($detail->sensori_bentuk ?? '-');
                        $set($detail->sensori_warna ?? '-');
                        $set($detail->sensori_aroma ?? '-');
                        $set($detail->sensori_rasa ?? '-');
                        $set($detail->sensori_tekstur ?? '-');

                        $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")
                            ->getAlignment()->setHorizontal('center');
                        $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")->getBorders()
                            ->getAllBorders()->setBorderStyle('thin');

                        $dataRow++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells("A6:{$lastCol}6");
                    $sheet->setCellValue('A6', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A6')->getFont()->setItalic(true);
                    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
                }

                // Auto width
                for ($i = 1; $i <= $totalCols; $i++) {
                    $letter = Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($letter)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(20);
            },
        ];
    }
}