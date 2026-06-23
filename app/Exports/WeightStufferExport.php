<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class WeightStufferExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Data Weight Stuffer';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ── Judul ──────────────────────────────────────────────────
                $sheet->mergeCells('A1:X1');
                $sheet->setCellValue('A1', 'LAPORAN PEMERIKSAAN WEIGHT STUFFER');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells('A2:X2');
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Header (row 4) ─────────────────────────────────────────
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Nama Produk',
                    'H' => 'Kode Prod',
                    'I' => 'Stuffer',
                    'J' => 'Kecepatan Stuffer',
                    'K' => 'Diameter Casing',
                    'L' => 'Std. Berat/3pcs (g)',
                    'M' => 'Aktual Berat/3pcs (g)',
                    'N' => 'Rata-rata Berat/3pcs (g)',
                    'O' => 'Status Berat',
                    'P' => 'Koreksi Berat',
                    'Q' => 'Ket. Berat',
                    'R' => 'Std. Panjang/pcs (mm)',
                    'S' => 'Aktual Panjang/pcs (mm)',
                    'T' => 'Rata-rata Panjang/pcs (mm)',
                    'U' => 'Status Panjang',
                    'V' => 'Koreksi Panjang',
                    'W' => 'Ket. Panjang',
                    'X' => 'Std. Berat Fla (g)',
                    'Y' => 'Aktual Berat Fla (g)',
                    'Z' => 'Rata-rata Berat Fla (g)',
                    'AA' => 'Status Fla',
                    'AB' => 'Koreksi Fla',
                    'AC' => 'Ket. Fla',
                    'AD' => 'Catatan',
                ];

                // Update merge judul ke AD
                $sheet->unmergeCells('A1:X1');
                $sheet->mergeCells('A1:AD1');
                $sheet->unmergeCells('A2:X2');
                $sheet->mergeCells('A2:AD2');

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setWrapText(true);
                }

                // Warna group header
                $groups = [
                    'L4:Q4' => 'D6E4F0', // berat
                    'R4:W4' => 'D6F0D6', // panjang
                    'X4:AC4' => 'F0F0D6', // fla
                ];
                foreach ($groups as $range => $color) {
                    $sheet->getStyle($range)->getFill()
                        ->setFillType('solid')
                        ->getStartColor()->setRGB($color);
                }

                // ── Data (mulai row 5) ─────────────────────────────────────
                $row = 5;
                $no  = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        $machine     = null;
                        $machineName = '-';

                        if ($detail->townsend)      { $machine = $detail->townsend;  $machineName = 'Townsend'; }
                        elseif ($detail->hitech)    { $machine = $detail->hitech;    $machineName = 'Hitech'; }
                        elseif ($detail->vemag)     { $machine = $detail->vemag;     $machineName = 'Vemag'; }
                        elseif ($detail->vemag2)    { $machine = $detail->vemag2;    $machineName = 'Vemag 2'; }
                        elseif ($detail->handtmann) { $machine = $detail->handtmann; $machineName = 'Handtmann'; }

                        $actualWeights = $detail->weights->pluck('actual_weight')->filter()->implode(', ');
                        $actualLongs   = $detail->weights->pluck('actual_long')->filter()->implode(', ');
                        $actualFlas    = $detail->weights->pluck('actual_fla')->filter()->implode(', ');
                        $caseDiameters = $detail->cases->pluck('actual_case_2')->filter()->implode(', ');

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$row}", $detail->time ?? '-');
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", trim(($detail->product->product_name ?? '-') . ' - ' . ($detail->gramase ?? '-')));
                        $sheet->setCellValue("H{$row}", $detail->production_code ?? '-');
                        $sheet->setCellValue("I{$row}", $machineName);
                        $sheet->setCellValue("J{$row}", $machine?->stuffer_speed ?? '-');
                        $sheet->setCellValue("K{$row}", $caseDiameters ?: '-');

                        // Berat
                        $sheet->setCellValue("L{$row}", $detail->weight_standard ?? '-');
                        $sheet->setCellValue("M{$row}", $actualWeights ?: '-');
                        $sheet->setCellValue("N{$row}", $machine?->avg_weight ?? '-');
                        $sheet->setCellValue("O{$row}", $detail->weight_status ?? '-');
                        $sheet->setCellValue("P{$row}", $detail->weight_corrective_action ?? '-');
                        $sheet->setCellValue("Q{$row}", $detail->weight_notes ?? '-');

                        // Panjang
                        $sheet->setCellValue("R{$row}", $detail->long_standard ?? '-');
                        $sheet->setCellValue("S{$row}", $actualLongs ?: '-');
                        $sheet->setCellValue("T{$row}", $machine?->avg_long ?? '-');
                        $sheet->setCellValue("U{$row}", $detail->long_status ?? '-');
                        $sheet->setCellValue("V{$row}", $detail->long_corrective_action ?? '-');
                        $sheet->setCellValue("W{$row}", $detail->long_notes ?? '-');

                        // Fla
                        $sheet->setCellValue("X{$row}", $detail->fla_standard ?? '-');
                        $sheet->setCellValue("Y{$row}", $actualFlas ?: '-');
                        $sheet->setCellValue("Z{$row}", $machine?->avg_fla ?? '-');
                        $sheet->setCellValue("AA{$row}", $detail->fla_status ?? '-');
                        $sheet->setCellValue("AB{$row}", $detail->fla_corrective_action ?? '-');
                        $sheet->setCellValue("AC{$row}", $detail->fla_notes ?? '-');

                        // Catatan mesin
                        $sheet->setCellValue("AD{$row}", $machine?->notes ?? '-');

                        $sheet->getStyle("A{$row}:AD{$row}")
                            ->getAlignment()->setHorizontal('center')->setWrapText(true);

                        $row++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells('A5:AD5');
                    $sheet->setCellValue('A5', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A5')->getFont()->setItalic(true);
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                    $row++;
                }

                // ── Border & auto width ────────────────────────────────────
                $sheet->getStyle("A4:AD" . ($row - 1))->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(40);
            },
        ];
    }
}