<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class WaterbathExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Waterbath'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Layout:
                // A-F   : No, Tanggal, Shift, QC, Group, Catatan
                // G-J   : Detail Produk (Nama Produk, Batch Code, Jumlah, Satuan)
                // K-T   : Pasteurisasi (10 kolom)
                // U-AA  : Cooling Shock (7 kolom)
                // AB-AF : Dripping (5 kolom)

                $lastCol = 'AF';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PASTEURISASI WATERBATH');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Row 4: Group header ────────────────────────────────────
                foreach (['A','B','C','D','E','F'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }
                $sheet->mergeCells('G4:J4');  $sheet->setCellValue('G4', 'Detail Produk');
                $sheet->mergeCells('K4:T4');  $sheet->setCellValue('K4', 'Pasteurisasi');
                $sheet->mergeCells('U4:AA4'); $sheet->setCellValue('U4', 'Cooling Shock');
                $sheet->mergeCells('AB4:AF4'); $sheet->setCellValue('AB4', 'Dripping');

                foreach (['G4','K4','U4','AB4'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal('center')->setVertical('center');
                }

                // ── Row 5: Sub-header ──────────────────────────────────────
                $infoLabels = [
                    'A' => 'No', 'B' => 'Tanggal', 'C' => 'Shift',
                    'D' => 'QC', 'E' => 'Group', 'F' => 'Catatan',
                ];
                foreach ($infoLabels as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $sub = [
                    // Detail
                    'G' => 'Nama Produk', 'H' => 'Batch Code',
                    'I' => 'Jumlah', 'J' => 'Satuan',
                    // Pasteurisasi
                    'K' => "Suhu Awal\nProduk (°C)",
                    'L' => "Suhu Awal\nAir (°C)",
                    'M' => 'Start',
                    'N' => 'Stop',
                    'O' => "Suhu Air\nSetelah Input\nPanel (°C)",
                    'P' => "Suhu Air\nSetelah Input\nAktual (°C)",
                    'Q' => "Suhu Air\nSetting (°C)",
                    'R' => "Suhu Air\nAktual (°C)",
                    'S' => "Suhu Akhir\nAir (°C)",
                    'T' => "Suhu Akhir\nProduk (°C)",
                    // Cooling Shock
                    'U' => "Suhu Awal\nAir (°C)",
                    'V' => 'Start',
                    'W' => 'Stop',
                    'X' => "Suhu Air\nSetting (°C)",
                    'Y' => "Suhu Air\nAktual (°C)",
                    'Z' => "Suhu Akhir\nAir (°C)",
                    'AA' => "Suhu Akhir\nProduk (°C)",
                    // Dripping
                    'AB' => 'Start',
                    'AC' => 'Stop',
                    'AD' => "Suhu\nZona Panas (°C)",
                    'AE' => "Suhu\nZona Dingin (°C)",
                    'AF' => "Suhu Akhir\nProduk (°C)",
                ];

                foreach ($sub as $col => $label) {
                    $sheet->setCellValue("{$col}5", $label);
                    $sheet->getStyle("{$col}5")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}5")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $sheet->getStyle("A4:{$lastCol}5")->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                // ── Data (mulai row 6) ─────────────────────────────────────
                $dataRow = 6;
                $no      = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    // Satu report bisa punya beberapa detail/pasteurisasi/cooling/dripping
                    // Gabungkan per index, maxRows = jumlah terbanyak
                    $details   = $report->details;
                    $pasteurs  = $report->pasteurisasi;
                    $coolings  = $report->coolingShocks;
                    $drippings = $report->drippings;

                    $maxRows = max(
                        $details->count(),
                        $pasteurs->count(),
                        $coolings->count(),
                        $drippings->count(),
                        1
                    );

                    for ($i = 0; $i < $maxRows; $i++) {
                        $det  = $details->get($i);
                        $pas  = $pasteurs->get($i);
                        $cool = $coolings->get($i);
                        $drip = $drippings->get($i);

                        $sheet->setCellValue("A{$dataRow}", $no);
                        $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$dataRow}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$dataRow}", $report->created_by ?? '-');
                        $sheet->setCellValue("E{$dataRow}", $shiftGroup ?: '-');
                        $sheet->setCellValue("F{$dataRow}", $det?->note ?? '-');
                        // Detail produk
                        $sheet->setCellValue("G{$dataRow}", $det?->product->product_name ?? '-');
                        $sheet->setCellValue("H{$dataRow}", $det?->batch_code ?? '-');
                        $sheet->setCellValue("I{$dataRow}", $det?->amount ?? '-');
                        $sheet->setCellValue("J{$dataRow}", $det?->unit ?? '-');
                        // Pasteurisasi
                        $sheet->setCellValue("K{$dataRow}", $pas?->initial_product_temp ?? '-');
                        $sheet->setCellValue("L{$dataRow}", $pas?->initial_water_temp ?? '-');
                        $sheet->setCellValue("M{$dataRow}", $pas?->start_time_pasteur ?? '-');
                        $sheet->setCellValue("N{$dataRow}", $pas?->stop_time_pasteur ?? '-');
                        $sheet->setCellValue("O{$dataRow}", $pas?->water_temp_after_input_panel ?? '-');
                        $sheet->setCellValue("P{$dataRow}", $pas?->water_temp_after_input_actual ?? '-');
                        $sheet->setCellValue("Q{$dataRow}", $pas?->water_temp_setting ?? '-');
                        $sheet->setCellValue("R{$dataRow}", $pas?->water_temp_actual ?? '-');
                        $sheet->setCellValue("S{$dataRow}", $pas?->water_temp_final ?? '-');
                        $sheet->setCellValue("T{$dataRow}", $pas?->product_temp_final ?? '-');
                        // Cooling Shock
                        $sheet->setCellValue("U{$dataRow}", $cool?->initial_water_temp ?? '-');
                        $sheet->setCellValue("V{$dataRow}", $cool?->start_time_pasteur ?? '-');
                        $sheet->setCellValue("W{$dataRow}", $cool?->stop_time_pasteur ?? '-');
                        $sheet->setCellValue("X{$dataRow}", $cool?->water_temp_setting ?? '-');
                        $sheet->setCellValue("Y{$dataRow}", $cool?->water_temp_actual ?? '-');
                        $sheet->setCellValue("Z{$dataRow}", $cool?->water_temp_final ?? '-');
                        $sheet->setCellValue("AA{$dataRow}", $cool?->product_temp_final ?? '-');
                        // Dripping
                        $sheet->setCellValue("AB{$dataRow}", $drip?->start_time_pasteur ?? '-');
                        $sheet->setCellValue("AC{$dataRow}", $drip?->stop_time_pasteur ?? '-');
                        $sheet->setCellValue("AD{$dataRow}", $drip?->hot_zone_temperature ?? '-');
                        $sheet->setCellValue("AE{$dataRow}", $drip?->cold_zone_temperature ?? '-');
                        $sheet->setCellValue("AF{$dataRow}", $drip?->product_temp_final ?? '-');

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

                $allCols = array_merge(array_keys($infoLabels), array_keys($sub));
                foreach ($allCols as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(45);
            },
        ];
    }
}