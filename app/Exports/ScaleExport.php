<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ScaleExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Timbangan'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'K';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI TIMBANGAN');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Pemeriksaan 1 & 2 masing-masing punya 3 titik ukur
                // P1: 1000g, 5000g, 10000g
                // P2: 1000g, 5000g, 10000g
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => "Jenis &\nKode Timbangan",
                    'H' => "Titik Ukur\n1000 gr\n(P1 / P2)",
                    'I' => "Titik Ukur\n5000 gr\n(P1 / P2)",
                    'J' => "Titik Ukur\n10000 gr\n(P1 / P2)",
                    'K' => 'Keterangan',
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
                        // Kelompokkan measurements per inspection_time_index & standard_weight
                        $byIndex = $detail->measurements->groupBy('inspection_time_index');
                        $p1 = $byIndex->get(1, collect())->keyBy('standard_weight');
                        $p2 = $byIndex->get(2, collect())->keyBy('standard_weight');

                        // Format: "P1 / P2" per titik ukur
                        $fmt = fn($w) => ($p1->get($w)?->measured_value ?? '-')
                            . ' / '
                            . ($p2->get($w)?->measured_value ?? '-');

                        $time = ($detail->time_1 && $detail->time_2)
                            ? Carbon::parse($detail->time_1)->format('H:i')
                              . ' - '
                              . Carbon::parse($detail->time_2)->format('H:i')
                            : (Carbon::parse($detail->time_1)->format('H:i') ?? '-');

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$row}", $time);
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}",
                            ($detail->scale->type ?? '-') . ' - ' . ($detail->scale->code ?? '-'));
                        $sheet->setCellValue("H{$row}", $fmt(1000));
                        $sheet->setCellValue("I{$row}", $fmt(5000));
                        $sheet->setCellValue("J{$row}", $fmt(10000));
                        $sheet->setCellValue("K{$row}", $detail->notes ?? '-');

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