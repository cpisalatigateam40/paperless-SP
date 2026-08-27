<?php

namespace App\Exports;

use Carbon\Carbon;
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $fixedHeaders = [
                    'No', 'Tanggal', 'Shift', 'Nama Produk', 'Kode Produk', 'Gramasi (gr)',
                    'Line Boiling Tank', 'Waktu Proses', 'Kode Produksi', 'Start', 'End',
                    'Suhu Adonan (°C)', 'Suhu Tangki I (°C)', 'Suhu Tangki II (°C)',
                ];
                $tailHeaders = ['Bentuk', 'Warna', 'Aroma', 'Rasa', 'Tekstur'];

                $groupLabels = [
                    "Berat Mentah (gr) - Std 11-12 gr",
                    "Actual Core Temp (°C) - Std 12°C",
                    "Berat Matang (gr) - Std 12 gr",
                    "Suhu After Cooling (°C)",
                ];

                $totalCols = count($fixedHeaders) + (count($groupLabels) * $this->maxChecks) + count($tailHeaders);
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

                foreach ($fixedHeaders as $label) {
                    $letter = Coordinate::stringFromColumnIndex($col);
                    $sheet->mergeCells("{$letter}4:{$letter}5");
                    $sheet->setCellValue("{$letter}4", $label);
                    $col++;
                }

                foreach ($groupLabels as $groupLabel) {
                    $startLetter = Coordinate::stringFromColumnIndex($col);
                    $endLetter = Coordinate::stringFromColumnIndex($col + $this->maxChecks - 1);
                    $sheet->mergeCells("{$startLetter}4:{$endLetter}4");
                    $sheet->setCellValue("{$startLetter}4", $groupLabel);

                    for ($i = 1; $i <= $this->maxChecks; $i++) {
                        $letter = Coordinate::stringFromColumnIndex($col);
                        $sheet->setCellValue("{$letter}5", $i);
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
                        $set($detail->aktual_suhu_tangki_2 ?? '-');

                        for ($i = 0; $i < $this->maxChecks; $i++) {
                            $set($detail->checks->get($i)?->berat_mentah ?? '-');
                        }
                        for ($i = 0; $i < $this->maxChecks; $i++) {
                            $set($detail->checks->get($i)?->actual_core_temp ?? '-');
                        }
                        for ($i = 0; $i < $this->maxChecks; $i++) {
                            $set($detail->checks->get($i)?->berat_matang ?? '-');
                        }
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