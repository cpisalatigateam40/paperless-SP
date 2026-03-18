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
                // A-G   : Info
                // H-Q   : Detail Produk (10 kolom)
                // R-V   : Item Formulasi (Nama Bahan, Kode Prod, Aktual, Sensori, Suhu)
                // W-AA  : Emulsifying (5 kolom)
                // AB-AE : Sensoric (4 kolom)
                // AF    : Tumbling
                // AG-AH : Aging

                $lastCol = 'AH';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PROSES PRODUKSI');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Row 4: Group header ────────────────────────────────────
                foreach (['A','B','C','D','E','F','G'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }
                $sheet->mergeCells('H4:Q4');  $sheet->setCellValue('H4', 'Detail Produk');
                $sheet->mergeCells('R4:V4');  $sheet->setCellValue('R4', 'Item Formulasi');
                $sheet->mergeCells('W4:AA4'); $sheet->setCellValue('W4', 'Emulsifying');
                $sheet->mergeCells('AB4:AE4'); $sheet->setCellValue('AB4', 'Sensoric');
                $sheet->mergeCells('AF4:AF5'); $sheet->setCellValue('AF4', 'Tumbling');
                $sheet->mergeCells('AG4:AH4'); $sheet->setCellValue('AG4', 'Aging');

                foreach (['H4','R4','W4','AB4','AG4'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal('center')->setVertical('center');
                }

                // ── Row 5: Sub-header ──────────────────────────────────────
                $infoLabels = [
                    'A' => 'No', 'B' => 'Tanggal', 'C' => 'Shift',
                    'D' => 'Time', 'E' => 'QC', 'F' => 'Group', 'G' => 'Section',
                ];
                foreach ($infoLabels as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $sub = [
                    // Detail Produk
                    'H' => 'Nama Produk',
                    'I' => 'Gramase',
                    'J' => 'Kode Prod',
                    'K' => 'Formula',
                    'L' => 'Waktu Mixing',
                    'M' => 'Produk Rework',
                    'N' => 'Rework (kg)',
                    'O' => 'Rework (%)',
                    'P' => 'Total Bahan (kg)',
                    'Q' => "Sensori\nHomo / Kekentalan / Aroma",
                    // Item Formulasi
                    'R' => 'Nama Bahan',
                    'S' => 'Kode Produksi',
                    'T' => 'Aktual (kg)',
                    'U' => 'Sensori',
                    'V' => 'Suhu (°C)',
                    // Emulsifying
                    'W' => "Std Suhu\nCampuran",
                    'X' => "Aktual 1 (°C)",
                    'Y' => "Aktual 2 (°C)",
                    'Z' => "Aktual 3 (°C)",
                    'AA' => "Rata-rata (°C)",
                    // Sensoric
                    'AB' => 'Homogenitas',
                    'AC' => 'Kekentalan',
                    'AD' => 'Aroma',
                    'AE' => 'Benda Asing',
                    // Aging
                    'AG' => 'Aging Process',
                    'AH' => 'Hasil Stuffing',
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
                    'A','B','C','D','E','F','G',             // Info
                    'H','I','J','K','L','M','N','O','P','Q', // Detail Produk
                    'W','X','Y','Z','AA',                    // Emulsifying
                    'AB','AC','AD','AE',                     // Sensoric
                    'AF',                                    // Tumbling
                    'AG','AH',                               // Aging
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
                        // Detail Produk
                        $sheet->setCellValue("H{$dataRow}", $detail->product->product_name ?? '-');
                        $sheet->setCellValue("I{$dataRow}", $detail->gramase ?? '-');
                        $sheet->setCellValue("J{$dataRow}", $detail->production_code ?? '-');
                        $sheet->setCellValue("K{$dataRow}", $detail->formula->formula_name ?? '-');
                        $sheet->setCellValue("L{$dataRow}", $detail->mixing_time ?? '-');
                        $sheet->setCellValue("M{$dataRow}", $detail->reworkProduct->product_name ?? '-');
                        $sheet->setCellValue("N{$dataRow}", $detail->rework_kg ?? '-');
                        $sheet->setCellValue("O{$dataRow}", $detail->rework_percent ?? '-');
                        $sheet->setCellValue("P{$dataRow}", $detail->total_material ?? '-');
                        $sheet->setCellValue("Q{$dataRow}", $sensoriDetail);
                        // Emulsifying
                        $sheet->setCellValue("W{$dataRow}", $emuls?->standard_mixture_temp ?? '-');
                        $sheet->setCellValue("X{$dataRow}", $emuls?->actual_mixture_temp_1 ?? '-');
                        $sheet->setCellValue("Y{$dataRow}", $emuls?->actual_mixture_temp_2 ?? '-');
                        $sheet->setCellValue("Z{$dataRow}", $emuls?->actual_mixture_temp_3 ?? '-');
                        $sheet->setCellValue("AA{$dataRow}", $emuls?->average_mixture_temp ?? '-');
                        // Sensoric
                        $sheet->setCellValue("AB{$dataRow}", $sens?->homogeneous ?? '-');
                        $sheet->setCellValue("AC{$dataRow}", $sens?->stiffness ?? '-');
                        $sheet->setCellValue("AD{$dataRow}", $sens?->aroma ?? '-');
                        $sheet->setCellValue("AE{$dataRow}", $sens?->foreign_object ?? '-');
                        // Tumbling
                        $sheet->setCellValue("AF{$dataRow}", $tumble?->tumbling_process ?? '-');
                        // Aging
                        $sheet->setCellValue("AG{$dataRow}", $aging?->aging_process ?? '-');
                        $sheet->setCellValue("AH{$dataRow}", $aging?->stuffing_result ?? '-');

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

                            $sheet->setCellValue("R{$itemRow}", $namaItem);
                            $sheet->setCellValue("S{$itemRow}", $item?->prod_code ?? '-');
                            $sheet->setCellValue("T{$itemRow}", $item?->actual_weight ?? '-');
                            $sheet->setCellValue("U{$itemRow}", $item?->sensory ?? '-');
                            $sheet->setCellValue("V{$itemRow}", $item?->temperature ?? '-');

                            $sheet->getStyle("R{$itemRow}:V{$itemRow}")->getAlignment()
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

                $allCols = array_merge(array_keys($infoLabels), array_keys($sub), ['AF']);
                foreach (array_unique($allCols) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(35);
            },
        ];
    }
}