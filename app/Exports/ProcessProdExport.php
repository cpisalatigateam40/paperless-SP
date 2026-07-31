<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ProcessProdExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Process Production'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Layout:
                // A-H    : Info (termasuk Catatan Report)
                // I-V    : Detail Produk (12 kolom + Hasil Penggilingan + Hasil Pencampuran)
                // W-AB   : Item Formulasi (Nama Bahan, Kode Prod, Aktual, Sensori, Suhu, Keterangan)
                // AC-AG  : Emulsifying (5 kolom)
                // AH-AK  : Sensoric (4 kolom)
                // AL-AN  : Tumbling (Proses, Lama Proses, Suhu Akhir)
                // AO-AP  : Aging

                $lastCol = 'AP';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PROSES PRODUKSI');
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
                $sheet->mergeCells('I4:V4');   $sheet->setCellValue('I4', 'Detail Produk');
                $sheet->mergeCells('W4:AB4');  $sheet->setCellValue('W4', 'Item Formulasi');
                $sheet->mergeCells('AC4:AG4'); $sheet->setCellValue('AC4', 'Emulsifying');
                $sheet->mergeCells('AH4:AK4'); $sheet->setCellValue('AH4', 'Sensoric');
                $sheet->mergeCells('AL4:AN4'); $sheet->setCellValue('AL4', 'Tumbling');
                $sheet->mergeCells('AO4:AP4'); $sheet->setCellValue('AO4', 'Aging');

                foreach (['I4','W4','AC4','AH4','AL4','AO4'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal('center')->setVertical('center');
                }

                // ── Row 5: Sub-header ──────────────────────────────────────
                $infoLabels = [
                    'A' => 'No', 'B' => 'Tanggal', 'C' => 'Shift',
                    'D' => 'Time', 'E' => 'QC', 'F' => 'Group', 'G' => 'Section',
                    'H' => 'Catatan Umum',
                ];
                foreach ($infoLabels as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $sub = [
                    // Detail Produk
                    'I' => 'Nama Produk',
                    'J' => 'Gramase',
                    'K' => 'Kode Prod',
                    'L' => 'Formula',
                    'M' => 'Waktu Mixing',
                    'N' => 'Produk Rework',
                    'O' => 'Rework (kg)',
                    'P' => 'Rework (%)',
                    'Q' => 'Total Bahan (kg)',
                    'R' => "Sensori\nHomo / Kekentalan / Aroma",
                    'S' => 'Catatan After Rework',
                    'T' => 'Nama Mesin',
                    'U' => 'Hasil Penggilingan',
                    'V' => 'Hasil Pencampuran',
                    // Item Formulasi
                    'W' => 'Nama Bahan',
                    'X' => 'Kode Produksi',
                    'Y' => 'Aktual (kg)',
                    'Z' => 'Sensori',
                    'AA' => 'Suhu (°C)',
                    'AB' => 'Keterangan',
                    // Emulsifying
                    'AC' => "Std Suhu\nCampuran",
                    'AD' => "Aktual 1 (°C)",
                    'AE' => "Aktual 2 (°C)",
                    'AF' => "Aktual 3 (°C)",
                    'AG' => "Rata-rata (°C)",
                    // Sensoric
                    'AH' => 'Homogenitas',
                    'AI' => 'Kekentalan',
                    'AJ' => 'Aroma',
                    'AK' => 'Benda Asing',
                    // Tumbling
                    'AL' => 'Proses Tumbling',
                    'AM' => 'Lama Proses (Menit)',
                    'AN' => 'Suhu Akhir (°C)',
                    // Aging
                    'AO' => 'Aging Process',
                    'AP' => 'Hasil Stuffing',
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

                // Kolom yang di-merge vertikal (tidak direpeat per item)
                $mergedCols = [
                    'A','B','C','D','E','F','G','H',                             // Info + Catatan Report
                    'I','J','K','L','M','N','O','P','Q','R','S','T','U','V',     // Detail Produk + Hasil Penggilingan/Pencampuran
                    'AC','AD','AE','AF','AG',                                    // Emulsifying
                    'AH','AI','AJ','AK',                                         // Sensoric
                    'AL','AM','AN',                                              // Tumbling
                    'AO','AP',                                                   // Aging
                ];

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->detail as $detail) {
                        $emuls  = $detail->emulsifying;
                        $sens   = $detail->sensoric;
                        $tumble = $detail->tumbling;
                        $aging  = $detail->aging;
                        $items  = $detail->items;

                        $maxItemRows = max($items->count(), 1);
                        $mergeEnd    = $dataRow + $maxItemRows - 1;

                        // Merge vertikal jika lebih dari 1 item
                        if ($maxItemRows > 1) {
                            foreach ($mergedCols as $col) {
                                $sheet->mergeCells("{$col}{$dataRow}:{$col}{$mergeEnd}");
                            }
                        }

                        // Sensori dari detail header (homogenity/stiffness/aroma)
                        $sensoriDetail = implode(' / ', array_filter([
                            $detail->sensory_homogenity ?? null,
                            $detail->sensory_stiffness  ?? null,
                            $detail->sensory_aroma      ?? null,
                        ])) ?: '-';

                        // Isi kolom non-item (sekali di baris pertama)
                        $sheet->setCellValue("A{$dataRow}", $no);
                        $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$dataRow}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$dataRow}", Carbon::parse($report->created_at)->format('H:i'));
                        $sheet->setCellValue("E{$dataRow}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$dataRow}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$dataRow}", $report->section->section_name ?? '-');
                        $sheet->setCellValue("H{$dataRow}", $report->notes ?? '-');
                        // Detail Produk
                        $sheet->setCellValue("I{$dataRow}", $detail->product->product_name ?? '-');
                        $sheet->setCellValue("J{$dataRow}", $detail->gramase ?? '-');
                        $sheet->setCellValue("K{$dataRow}", $detail->production_code ?? '-');
                        $sheet->setCellValue("L{$dataRow}", $detail->formula->formula_name ?? '-');
                        $sheet->setCellValue("M{$dataRow}", $detail->mixing_time ?? '-');
                        $sheet->setCellValue("N{$dataRow}", $detail->reworkProduct->product_name ?? '-');
                        $sheet->setCellValue("O{$dataRow}", $detail->rework_kg ?? '-');
                        $sheet->setCellValue("P{$dataRow}", $detail->rework_percent ?? '-');
                        $sheet->setCellValue("Q{$dataRow}", $detail->total_material ?? '-');
                        $sheet->setCellValue("R{$dataRow}", $sensoriDetail);
                        $sheet->setCellValue("S{$dataRow}", $detail->notes ?? '-');
                        $sheet->setCellValue("T{$dataRow}", $detail->machine_name ?? '-');
                        $sheet->setCellValue("U{$dataRow}", $detail->hasil_penggilingan ?? '-');
                        $sheet->setCellValue("V{$dataRow}", $detail->hasil_pencampuran ?? '-');
                        // Emulsifying
                        $sheet->setCellValue("AC{$dataRow}", $emuls?->standard_mixture_temp ?? '-');
                        $sheet->setCellValue("AD{$dataRow}", $emuls?->actual_mixture_temp_1 ?? '-');
                        $sheet->setCellValue("AE{$dataRow}", $emuls?->actual_mixture_temp_2 ?? '-');
                        $sheet->setCellValue("AF{$dataRow}", $emuls?->actual_mixture_temp_3 ?? '-');
                        $sheet->setCellValue("AG{$dataRow}", $emuls?->average_mixture_temp ?? '-');
                        // Sensoric
                        $sheet->setCellValue("AH{$dataRow}", $sens?->homogeneous ?? '-');
                        $sheet->setCellValue("AI{$dataRow}", $sens?->stiffness ?? '-');
                        $sheet->setCellValue("AJ{$dataRow}", $sens?->aroma ?? '-');
                        $sheet->setCellValue("AK{$dataRow}", $sens?->foreign_object ?? '-');
                        // Tumbling
                        $sheet->setCellValue("AL{$dataRow}", $tumble?->tumbling_process ?? '-');
                        $sheet->setCellValue("AM{$dataRow}", $tumble?->process_duration ?? '-');
                        $sheet->setCellValue("AN{$dataRow}", $tumble?->final_temperature ?? '-');
                        // Aging
                        $sheet->setCellValue("AO{$dataRow}", $aging?->aging_process ?? '-');
                        $sheet->setCellValue("AP{$dataRow}", $aging?->stuffing_result ?? '-');

                        // Style merged cols: center + middle
                        foreach ($mergedCols as $col) {
                            $sheet->getStyle("{$col}{$dataRow}")->getAlignment()
                                ->setHorizontal('center')->setVertical('center');
                        }

                        // Loop per item formulasi
                        for ($itemIdx = 0; $itemIdx < $maxItemRows; $itemIdx++) {
                            $item    = $items->get($itemIdx);
                            $itemRow = $dataRow + $itemIdx;

                            $namaItem = $item
                                ? ($item->formulation?->rawMaterial?->material_name
                                    ?? $item->formulation?->premix?->name
                                    ?? '-')
                                : '-';

                            $sheet->setCellValue("W{$itemRow}", $namaItem);
                            $sheet->setCellValue("X{$itemRow}", $item?->prod_code ?? '-');
                            $sheet->setCellValue("Y{$itemRow}", $item?->actual_weight ?? '-');
                            $sheet->setCellValue("Z{$itemRow}", $item?->sensory ?? '-');
                            $sheet->setCellValue("AA{$itemRow}", $item?->temperature ?? '-');
                            $sheet->setCellValue("AB{$itemRow}", $item?->keterangan ?? '-');

                            $sheet->getStyle("W{$itemRow}:AB{$itemRow}")->getAlignment()
                                ->setHorizontal('center')->setVertical('center');
                            $sheet->getStyle("A{$itemRow}:{$lastCol}{$itemRow}")->getBorders()
                                ->getAllBorders()->setBorderStyle('thin');
                        }

                        $dataRow += $maxItemRows;
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
                foreach (array_unique($allCols) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(35);
            },
        ];
    }
}