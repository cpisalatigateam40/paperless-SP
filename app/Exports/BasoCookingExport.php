<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class BasoCookingExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Baso Cooking'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // A-H   : No, Tanggal, Shift, Time, QC, Group, Nama Produk, Kode Prod
                // I-L   : Standar & Setting
                // M-P   : Data Detail
                // Q-W   : Suhu Baso Awal (waktu, temp 1-5, avg) = 7 kolom
                // X-AD  : Suhu Baso Akhir (waktu, temp 1-5, avg) = 7 kolom
                // AE-AI : Sensori (bentuk, rasa, aroma, tekstur, warna)
                // AJ    : Berat Akhir

                $lastCol = 'AJ';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PEMASAKAN BASO');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Row 4: Group header ────────────────────────────────────
                foreach (['A','B','C','D','E','F','G','H'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }
                $sheet->mergeCells('I4:L4');   $sheet->setCellValue('I4', 'Standar & Setting');
                $sheet->mergeCells('M4:P4');   $sheet->setCellValue('M4', 'Data Detail');
                $sheet->mergeCells('Q4:W4');   $sheet->setCellValue('Q4', 'Suhu Baso Awal');
                $sheet->mergeCells('X4:AD4');  $sheet->setCellValue('X4', 'Suhu Baso Akhir');
                $sheet->mergeCells('AE4:AI4'); $sheet->setCellValue('AE4', 'Sensori');
                $sheet->mergeCells('AJ4:AJ5');

                foreach (['I4','M4','Q4','X4','AE4'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal('center')->setVertical('center');
                }

                // ── Row 5: Sub-header ──────────────────────────────────────
                $headerInfoLabels = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Nama Produk',
                    'H' => 'Kode Prod',
                ];

                foreach ($headerInfoLabels as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $row5 = [
                    'I'  => "Std Suhu\nPusat (°C)",
                    'J'  => "Std Berat\nAkhir",
                    'K'  => "Set Tangki\nPerebusan 1 (°C)",
                    'L'  => "Set Tangki\nPerebusan 2 (°C)",
                    'M'  => "Suhu\nEmulsi (°C)",
                    'N'  => "Suhu Air\nTangki 1 (°C)",
                    'O'  => "Suhu Air\nTangki 2 (°C)",
                    'P'  => "Berat\nAwal (kg)",
                    // Suhu Baso Awal
                    'Q'  => 'Waktu',
                    'R'  => 'Suhu 1',
                    'S'  => 'Suhu 2',
                    'T'  => 'Suhu 3',
                    'U'  => 'Suhu 4',
                    'V'  => 'Suhu 5',
                    'W'  => 'Rata-rata',
                    // Suhu Baso Akhir
                    'X'  => 'Waktu',
                    'Y'  => 'Suhu 1',
                    'Z'  => 'Suhu 2',
                    'AA' => 'Suhu 3',
                    'AB' => 'Suhu 4',
                    'AC' => 'Suhu 5',
                    'AD' => 'Rata-rata',
                    // Sensori
                    'AE' => 'Bentuk',
                    'AF' => 'Rasa',
                    'AG' => 'Aroma',
                    'AH' => 'Tekstur',
                    'AI' => 'Warna',
                ];

                foreach ($row5 as $col => $label) {
                    $sheet->setCellValue("{$col}5", $label);
                    $sheet->getStyle("{$col}5")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}5")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }
                $sheet->setCellValue('AJ4', "Berat\nAkhir (kg)");
                $sheet->getStyle('AJ4')->getFont()->setBold(true);
                $sheet->getStyle('AJ4')->getAlignment()
                    ->setHorizontal('center')->setVertical('center')->setWrapText(true);

                $sheet->getStyle("A4:{$lastCol}5")->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                // ── Data (mulai row 6) ─────────────────────────────────────
                $dataRow = 6;
                $no      = 1;

                $sensoriVal = fn($v) => match((string)($v ?? '')) {
                    '1', 'OK'       => 'OK',
                    '0', 'Tidak OK' => 'Tidak OK',
                    default         => $v ?? '-',
                };

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        $tempAwal  = $detail->temperatures->firstWhere('time_type', 'awal');
                        $tempAkhir = $detail->temperatures->firstWhere('time_type', 'akhir');

                        $sheet->setCellValue("A{$dataRow}", $no);
                        $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$dataRow}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$dataRow}", Carbon::parse($report->created_at)->format('H:i'));
                        $sheet->setCellValue("E{$dataRow}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$dataRow}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$dataRow}", $report->product->product_name ?? '-');
                        $sheet->setCellValue("H{$dataRow}", $detail->production_code ?? '-');
                        // Standar & Setting
                        $sheet->setCellValue("I{$dataRow}", $report->std_core_temp ?? '-');
                        $sheet->setCellValue("J{$dataRow}", $report->std_weight ?? '-');
                        $sheet->setCellValue("K{$dataRow}", $report->set_boiling_1 ?? '-');
                        $sheet->setCellValue("L{$dataRow}", $report->set_boiling_2 ?? '-');
                        // Data detail
                        $sheet->setCellValue("M{$dataRow}", $detail->emulsion_temp ?? '-');
                        $sheet->setCellValue("N{$dataRow}", $detail->boiling_tank_temp_1 ?? '-');
                        $sheet->setCellValue("O{$dataRow}", $detail->boiling_tank_temp_2 ?? '-');
                        $sheet->setCellValue("P{$dataRow}", $detail->initial_weight ?? '-');
                        // Suhu baso awal
                        $sheet->setCellValue("Q{$dataRow}", $tempAwal?->time_recorded ?? '-');
                        $sheet->setCellValue("R{$dataRow}", $tempAwal?->baso_temp_1 ?? '-');
                        $sheet->setCellValue("S{$dataRow}", $tempAwal?->baso_temp_2 ?? '-');
                        $sheet->setCellValue("T{$dataRow}", $tempAwal?->baso_temp_3 ?? '-');
                        $sheet->setCellValue("U{$dataRow}", $tempAwal?->baso_temp_4 ?? '-');
                        $sheet->setCellValue("V{$dataRow}", $tempAwal?->baso_temp_5 ?? '-');
                        $sheet->setCellValue("W{$dataRow}", $tempAwal?->avg_baso_temp ?? '-');
                        // Suhu baso akhir
                        $sheet->setCellValue("X{$dataRow}", $tempAkhir?->time_recorded ?? '-');
                        $sheet->setCellValue("Y{$dataRow}", $tempAkhir?->baso_temp_1 ?? '-');
                        $sheet->setCellValue("Z{$dataRow}", $tempAkhir?->baso_temp_2 ?? '-');
                        $sheet->setCellValue("AA{$dataRow}", $tempAkhir?->baso_temp_3 ?? '-');
                        $sheet->setCellValue("AB{$dataRow}", $tempAkhir?->baso_temp_4 ?? '-');
                        $sheet->setCellValue("AC{$dataRow}", $tempAkhir?->baso_temp_5 ?? '-');
                        $sheet->setCellValue("AD{$dataRow}", $tempAkhir?->avg_baso_temp ?? '-');
                        // Sensori
                        $sheet->setCellValue("AE{$dataRow}", $sensoriVal($detail->sensory_shape));
                        $sheet->setCellValue("AF{$dataRow}", $sensoriVal($detail->sensory_taste));
                        $sheet->setCellValue("AG{$dataRow}", $sensoriVal($detail->sensory_aroma));
                        $sheet->setCellValue("AH{$dataRow}", $sensoriVal($detail->sensory_texture));
                        $sheet->setCellValue("AI{$dataRow}", $sensoriVal($detail->sensory_color));
                        // Berat akhir
                        $sheet->setCellValue("AJ{$dataRow}", $detail->final_weight ?? '-');

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

                $allCols = array_merge(array_keys($headerInfoLabels), array_keys($row5));
                foreach ($allCols as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(40);
            },
        ];
    }
}