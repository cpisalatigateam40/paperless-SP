<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class PackagingVerifExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Verifikasi Kemasan'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'X';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI KEMASAN PLASTIK');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Nama Produk',
                    'H' => 'In Cutting',
                    'I' => 'Pengemasan',
                    'J' => 'Jumlah Sampling',
                    'K' => 'Hasil Sampling',
                    'L' => "Kondisi Seal\n(1-5)",
                    'M' => "Vacuum\n(1-5)",
                    'N' => "Isi Per Pack\n(1-5)",
                    'O' => "Std Panjang/pcs",
                    'P' => "Aktual Panjang/pcs\n(1-5)",
                    'Q' => "Rata-rata Panjang/pcs",
                    'R' => "Std Berat/pcs",
                    'S' => "Aktual Berat/pcs\n(1-5)",
                    'T' => "Rata-rata Berat/pcs",
                    'U' => "Std Berat/pack",
                    'V' => "Aktual Berat/pack\n(1-5)",
                    'W' => "Rata-rata Berat/pack",
                    'X' => 'Verifikasi MD',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setWrapText(true);
                }

                $row = 5;
                $no  = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        $cl = $detail->checklist;

                        // Helper: gabungkan nilai aktual 1-5 jadi satu string
                        $join = fn(string $prefix) => collect(range(1, 5))
                            ->map(fn($i) => $cl?->{"{$prefix}_{$i}"} ?? null)
                            ->filter(fn($v) => $v !== null && $v !== '')
                            ->implode(', ');

                        // In cutting: cek manual_1 atau machine_1
                        $inCutting = $cl?->in_cutting_manual_1 ? 'Manual'
                            : ($cl?->in_cutting_machine_1 ? 'Mesin' : '-');

                        // Packaging: cek thermoformer_1 atau manual_1
                        $packaging = $cl?->packaging_thermoformer_1 ? 'Thermoformer'
                            : ($cl?->packaging_manual_1 ? 'Manual' : '-');

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$row}", $detail->time ?? '-');
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", $detail->product->product_name ?? '-');
                        $sheet->setCellValue("H{$row}", $inCutting);
                        $sheet->setCellValue("I{$row}", $packaging);
                        $sheet->setCellValue("J{$row}", $cl?->sampling_amount ?? '-');
                        $sheet->setCellValue("K{$row}", $cl?->sampling_result ?? '-');
                        $sheet->setCellValue("L{$row}", $join('sealing_condition') ?: '-');
                        $sheet->setCellValue("M{$row}", $join('sealing_vacuum') ?: '-');
                        $sheet->setCellValue("N{$row}", $join('content_per_pack') ?: '-');
                        $sheet->setCellValue("O{$row}", $cl?->standard_long_pcs ?? '-');
                        $sheet->setCellValue("P{$row}", $join('actual_long_pcs') ?: '-');
                        $sheet->setCellValue("Q{$row}", $cl?->avg_long_pcs ?? '-');
                        $sheet->setCellValue("R{$row}", $cl?->standard_weight_pcs ?? '-');
                        $sheet->setCellValue("S{$row}", $join('actual_weight_pcs') ?: '-');
                        $sheet->setCellValue("T{$row}", $cl?->avg_weight_pcs ?? '-');
                        $sheet->setCellValue("U{$row}", $cl?->standard_weight ?? '-');
                        $sheet->setCellValue("V{$row}", $join('actual_weight') ?: '-');
                        $sheet->setCellValue("W{$row}", $cl?->avg_weight ?? '-');
                        $sheet->setCellValue("X{$row}", $cl?->verif_md ?? '-');

                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                            ->getAlignment()->setHorizontal('center');

                        $row++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells("A5:{$lastCol}5");
                    $sheet->setCellValue('A5', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A5')->getFont()->setItalic(true);
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                    $row++;
                }

                $sheet->getStyle("A4:{$lastCol}" . ($row - 1))->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(45);
            },
        ];
    }
}