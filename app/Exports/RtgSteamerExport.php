<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class RtgSteamerExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'RTG Steamer'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'R';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PEMASAKAN DENGAN STEAMER');
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
                    'H' => 'Kode Prod',
                    'I' => 'Steamer',
                    'J' => 'Jml Trolley',
                    'K' => 'Suhu Ruang (°C)',
                    'L' => 'Suhu Produk (°C)',
                    'M' => 'Waktu (mnt)',
                    'N' => 'Sensori Kematangan',
                    'O' => 'Sensori Rasa',
                    'P' => 'Sensori Aroma',
                    'Q' => 'Sensori Tekstur',
                    'R' => 'Sensori Warna',
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
                        $time = ($detail->start_time && $detail->end_time)
                            ? $detail->start_time . ' - ' . $detail->end_time
                            : ($detail->start_time ?? '-');

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$row}", $time);
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", $report->product->product_name ?? '-');
                        $sheet->setCellValue("H{$row}", $detail->production_code ?? '-');
                        $sheet->setCellValue("I{$row}", $detail->steamer ?? '-');
                        $sheet->setCellValue("J{$row}", $detail->trolley_count ?? '-');
                        $sheet->setCellValue("K{$row}", $detail->room_temp ?? '-');
                        $sheet->setCellValue("L{$row}", $detail->product_temp ?? '-');
                        $sheet->setCellValue("M{$row}", $detail->time_minute ?? '-');
                        $sheet->setCellValue("N{$row}", $detail->sensory_ripeness ?? '-');
                        $sheet->setCellValue("O{$row}", $detail->sensory_taste ?? '-');
                        $sheet->setCellValue("P{$row}", $detail->sensory_aroma ?? '-');
                        $sheet->setCellValue("Q{$row}", $detail->sensory_texture ?? '-');
                        $sheet->setCellValue("R{$row}", $detail->sensory_color ?? '-');

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

                $sheet->getRowDimension(4)->setRowHeight(35);
            },
        ];
    }
}