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
                $sheet->mergeCells('A1:R1');
                $sheet->setCellValue('A1', 'LAPORAN PEMERIKSAAN WEIGHT STUFFER');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells('A2:R2');
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
                    'O' => 'Std. Panjang/pcs (cm)',
                    'P' => 'Aktual Panjang/pcs (cm)',
                    'Q' => 'Rata-rata Panjang/pcs (cm)',
                    'R' => 'Catatan',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setWrapText(true);
                }

                // ── Data (mulai row 5) ─────────────────────────────────────
                $row = 5;
                $no  = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        // Tentukan mesin & ambil data mesin
                        $machine = null;
                        if ($detail->townsend) {
                            $machine     = $detail->townsend;
                            $machineName = 'Townsend';
                        } elseif ($detail->hitech) {
                            $machine     = $detail->hitech;
                            $machineName = 'Hitech';
                        } else {
                            $machineName = '-';
                        }

                        // Aktual berat: gabungkan semua WeightStufferMeasurement
                        $actualWeights = $detail->weights
                            ->pluck('actual_weight')
                            ->filter()
                            ->implode(', ');

                        $actualLongs = $detail->weights
                            ->pluck('actual_long')
                            ->filter()
                            ->implode(', ');

                        // Diameter casing: dari cases->actual_case_2 (semua digabung)
                        $caseDiameters = $detail->cases
                            ->pluck('actual_case_2')
                            ->filter()
                            ->implode(', ');

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$row}", $detail->time ?? '-');
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", $detail->product->product_name ?? '-');
                        $sheet->setCellValue("H{$row}", $detail->production_code ?? '-');
                        $sheet->setCellValue("I{$row}", $machineName);
                        $sheet->setCellValue("J{$row}", $machine?->stuffer_speed ?? '-');
                        $sheet->setCellValue("K{$row}", $caseDiameters ?: '-');
                        $sheet->setCellValue("L{$row}", $detail->weight_standard ?? '-');
                        $sheet->setCellValue("M{$row}", $actualWeights ?: '-');
                        $sheet->setCellValue("N{$row}", $machine?->avg_weight ?? '-');
                        $sheet->setCellValue("O{$row}", $detail->long_standard ?? '-');
                        $sheet->setCellValue("P{$row}", $actualLongs ?: '-');
                        $sheet->setCellValue("Q{$row}", $machine?->avg_long ?? '-');
                        $sheet->setCellValue("R{$row}", $machine?->notes ?? '-');

                        $sheet->getStyle("A{$row}:R{$row}")
                            ->getAlignment()->setHorizontal('center');

                        $row++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells('A5:R5');
                    $sheet->setCellValue('A5', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A5')->getFont()->setItalic(true);
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                    $row++;
                }

                // ── Border & auto width ────────────────────────────────────
                $sheet->getStyle("A4:R" . ($row - 1))->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(40);
            },
        ];
    }
}