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

    public function title(): string
    {
        return 'Freez & Packaging';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $lastCol = 'AD';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue(
                    'A1',
                    'LAPORAN VERIFIKASI PEMBEKUAN IQF & PENGEMASAN KARTON BOX'
                );

                $sheet->getStyle('A1')->getFont()
                    ->setBold(true)
                    ->setSize(13);

                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);

                $sheet->getStyle('A2')->getAlignment()
                    ->setHorizontal('center');

                $headers = [
                    'A'  => 'No',
                    'B'  => 'Tanggal',
                    'C'  => 'Shift',
                    'D'  => 'Jam',
                    'E'  => 'QC',
                    'F'  => 'Nama Produk',
                    'G'  => 'Gramase',
                    'H'  => 'Kode Produksi',
                    'I'  => 'Best Before',

                    'J'  => 'Tipe Mesin',
                    'K'  => 'Mesin IQF',

                    'L'  => 'Std Suhu Produk',
                    'M'  => 'Suhu Aktual Produk',

                    'N'  => 'Room IQF',
                    'O'  => 'Suction IQF',

                    'P'  => 'Durasi Display',
                    'Q'  => 'Durasi Aktual',

                    'R'  => 'Notes Freezing',

                    'S'  => 'Kondisi Karton',
                    'T'  => 'Kondisi Label',

                    'U'  => 'Isi Bag',
                    'V'  => 'Isi Binded',
                    'W'  => 'Isi RTG',

                    'X'  => 'Std Berat Karton',

                    'Y'  => 'Berat Karton 1',
                    'Z'  => 'Berat Karton 2',
                    'AA' => 'Berat Karton 3',
                    'AB' => 'Berat Karton 4',
                    'AC' => 'Berat Karton 5',

                    'AD' => 'Rata-rata',

                    'AE' => 'Notes Kartoning',

                    'AF' => 'Release Status',
                    'AG' => 'Tindakan Koreksi',
                    'AH' => 'Verifikasi',
                    'AI' => 'Notes Detail',

                    'AJ' => 'Notes Report',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);

                    $sheet->getStyle("{$col}4")
                        ->getFont()
                        ->setBold(true);

                    $sheet->getStyle("{$col}4")
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setHorizontal('center');
                }

                $row = 5;
                $no = 1;

                foreach ($this->reports as $report) {

                    foreach ($report->details as $detail) {

                        $frz = $detail->freezing;
                        $krt = $detail->kartoning;

                        $time = '';

                        if ($detail->start_time || $detail->end_time) {
                            $time =
                                ($detail->start_time ?? '-') .
                                ' - ' .
                                ($detail->end_time ?? '-');
                        }

                        $actualTemps = '-';

                        if ($frz && $frz->actualTemps->count()) {

                            $actualTemps = $frz->actualTemps
                                ->pluck('actual_temp')
                                ->map(fn($v) => number_format($v, 2))
                                ->implode(', ');
                        }

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}",
                            Carbon::parse($report->date)->format('d/m/Y'));

                        $sheet->setCellValue("C{$row}", $report->shift);

                        $sheet->setCellValue("D{$row}", $time);

                        $sheet->setCellValue("E{$row}",
                            $report->created_by ?? '-');

                        $sheet->setCellValue("F{$row}",
                            $detail->product->product_name ?? '-');

                        $sheet->setCellValue("G{$row}",
                            $detail->gramase
                            ?? $detail->product->nett_weight
                            ?? '-');

                        $sheet->setCellValue("H{$row}",
                            $detail->production_code ?? '-');

                        $sheet->setCellValue("I{$row}",
                            $detail->best_before
                                ? Carbon::parse($detail->best_before)->format('d/m/Y')
                                : '-');

                        $sheet->setCellValue("J{$row}",
                            $frz?->machine_type ?? '-');

                        $sheet->setCellValue("K{$row}",
                            $frz?->iqf_machine ?? '-');

                        $sheet->setCellValue("L{$row}",
                            $frz?->standard_temp ?? '-');

                        $sheet->setCellValue("M{$row}",
                            $actualTemps);

                        $sheet->setCellValue("N{$row}",
                            $frz?->iqf_room_temp ?? '-');

                        $sheet->setCellValue("O{$row}",
                            $frz?->iqf_suction_temp ?? '-');

                        $sheet->setCellValue("P{$row}",
                            $frz?->freezing_time_display ?? '-');

                        $sheet->setCellValue("Q{$row}",
                            $frz?->freezing_time_actual ?? '-');

                        $sheet->setCellValue("R{$row}",
                            $frz?->notes ?? '-');

                        $sheet->setCellValue("S{$row}",
                            $krt?->carton_condition ?? '-');

                        $sheet->setCellValue("T{$row}",
                            $krt?->label_condition ?? '-');

                        $sheet->setCellValue("U{$row}",
                            $krt?->content_bag ?? '-');

                        $sheet->setCellValue("V{$row}",
                            $krt?->content_binded ?? '-');

                        $sheet->setCellValue("W{$row}",
                            $krt?->content_rtg ?? '-');

                        $sheet->setCellValue("X{$row}",
                            $krt?->carton_weight_standard ?? '-');

                        $sheet->setCellValue("Y{$row}",
                            $krt?->weight_1 ?? '-');

                        $sheet->setCellValue("Z{$row}",
                            $krt?->weight_2 ?? '-');

                        $sheet->setCellValue("AA{$row}",
                            $krt?->weight_3 ?? '-');

                        $sheet->setCellValue("AB{$row}",
                            $krt?->weight_4 ?? '-');

                        $sheet->setCellValue("AC{$row}",
                            $krt?->weight_5 ?? '-');

                        $sheet->setCellValue("AD{$row}",
                            $krt?->avg_weight ?? '-');

                        $sheet->setCellValue("AE{$row}",
                            $krt?->notes ?? '-');

                        $sheet->setCellValue("AF{$row}",
                            $detail->release_status ?? '-');

                        $sheet->setCellValue("AG{$row}",
                            $detail->corrective_action ?? '-');

                        $sheet->setCellValue("AH{$row}",
                            $detail->verif_after ?? '-');

                        $sheet->setCellValue("AI{$row}",
                            $detail->notes ?? '-');

                        $sheet->setCellValue("AJ{$row}",
                            $report->notes ?? '-');

                        $row++;
                        $no++;
                    }
                }

                $sheet->getStyle("A4:AJ" . ($row - 1))
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');

                foreach (range('A', 'Z') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                foreach (['AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(35);
            },
        ];
    }
}