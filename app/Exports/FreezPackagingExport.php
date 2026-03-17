<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class FreezPackagingExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Freez & Packaging'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'U';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PEMBEKUAN IQF & PENGEMASAN KARTON BOX');
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
                    'I' => 'Best Before',
                    'J' => "Std Suhu\nAfter IQF (°C)",
                    'K' => "Aktual Suhu\nAfter IQF (°C)",
                    'L' => "Setting\nRoom IQF (°C)",
                    'M' => "Setting\nSuction IQF (°C)",
                    'N' => "Durasi Frz\nDisplay (mnt)",
                    'O' => "Durasi Frz\nAktual (mnt)",
                    'P' => 'Kondisi Karton',
                    'Q' => "Kesesuaian Isi\nPer Box/Binded/Inner",
                    'R' => "Std Berat\nKarton (kg)",
                    'S' => "Aktual Berat\nKarton 1-5 (kg)",
                    'T' => "Rata-rata\nBerat Karton (kg)",
                    'U' => 'Keterangan',
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
                        $frz = $detail->freezing;
                        $krt = $detail->kartoning;

                        // Time: start_time - end_time
                        $time = ($detail->start_time && $detail->end_time)
                            ? $detail->start_time . ' - ' . $detail->end_time
                            : ($detail->start_time ?? '-');

                        // Aktual berat 1-5 gabung koma
                        $aktualBerat = collect(range(1, 5))
                            ->map(fn($i) => $krt?->{"weight_{$i}"} ?? null)
                            ->filter(fn($v) => $v !== null && $v !== '')
                            ->implode(', ');

                        // Kesesuaian isi: gabung bag/binded/inner
                        $isiParts = array_filter([
                            $krt?->content_bag    ? "Bag: {$krt->content_bag}"       : null,
                            $krt?->content_binded ? "Binded: {$krt->content_binded}" : null,
                            $krt?->content_rtg    ? "Inner: {$krt->content_rtg}"     : null,
                        ]);
                        $isiBox = implode(', ', $isiParts) ?: '-';

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$row}", $time);
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", $detail->product->product_name ?? '-');
                        $sheet->setCellValue("H{$row}", $detail->production_code ?? '-');
                        $sheet->setCellValue("I{$row}", $detail->best_before
                            ? Carbon::parse($detail->best_before)->format('d/m/Y') : '-');
                        $sheet->setCellValue("J{$row}", $frz?->standard_temp ?? '-');
                        $sheet->setCellValue("K{$row}", $frz?->end_product_temp ?? '-');
                        $sheet->setCellValue("L{$row}", $frz?->iqf_room_temp ?? '-');
                        $sheet->setCellValue("M{$row}", $frz?->iqf_suction_temp ?? '-');
                        $sheet->setCellValue("N{$row}", $frz?->freezing_time_display ?? '-');
                        $sheet->setCellValue("O{$row}", $frz?->freezing_time_actual ?? '-');
                        $sheet->setCellValue("P{$row}", $krt?->carton_condition ?? '-');
                        $sheet->setCellValue("Q{$row}", $isiBox);
                        $sheet->setCellValue("R{$row}", $krt?->carton_weight_standard ?? '-');
                        $sheet->setCellValue("S{$row}", $aktualBerat ?: '-');
                        $sheet->setCellValue("T{$row}", $krt?->avg_weight ?? '-');
                        $sheet->setCellValue("U{$row}", $detail->corrective_action ?? '-');

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

                $sheet->getRowDimension(4)->setRowHeight(40);
            },
        ];
    }
}