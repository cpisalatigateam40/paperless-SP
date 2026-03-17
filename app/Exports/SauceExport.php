<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class SauceExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Data Sauce'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'W';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN PEMASAKAN PRODUK DI STEAM KETTLE (SAUCE)');
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
                    'I' => 'Waktu Start',
                    'J' => 'Waktu Stop',
                    'K' => 'Bahan',
                    'L' => 'Berat',
                    'M' => 'Sensori RM',
                    'N' => 'Lama Proses',
                    'O' => 'Mixing Paddle',
                    'P' => 'Pressure',
                    'Q' => 'Target Temp. (°C)',
                    'R' => 'Aktual Temp. (°C)',
                    'S' => 'Sensori Warna',
                    'T' => 'Sensori Aroma',
                    'U' => 'Sensori Rasa',
                    'V' => 'Sensori Tekstur',
                    'W' => 'Catatan',
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
                        $bahanList = $detail->rawMaterials
                            ->map(fn($rm) => $rm->material_type === 'premix'
                                ? ($rm->premix->name ?? '-')
                                : ($rm->rawMaterial->material_name ?? '-'))
                            ->implode(', ');

                        $beratList = $detail->rawMaterials
                            ->map(fn($rm) => $rm->amount ?? '-')
                            ->implode(', ');

                        $sensoriRmList = $detail->rawMaterials
                            ->map(fn($rm) => $rm->sensory ?? '-')
                            ->implode(', ');

                        $mixingPaddle = '-';
                        if ($detail->mixing_paddle_on)  $mixingPaddle = 'On';
                        if ($detail->mixing_paddle_off) $mixingPaddle = 'Off';

                        $time = ($report->start_time && $report->end_time)
                            ? $report->start_time . ' - ' . $report->end_time
                            : ($report->start_time ?? '-');

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$row}", $detail->time ?? '-');
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", $report->product->product_name ?? '-');
                        $sheet->setCellValue("H{$row}", $report->production_code ?? '-');
                        $sheet->setCellValue("I{$row}", $report->start_time ?? '-');
                        $sheet->setCellValue("J{$row}", $report->end_time ?? '-');
                        $sheet->setCellValue("K{$row}", $bahanList ?: '-');
                        $sheet->setCellValue("L{$row}", $beratList ?: '-');
                        $sheet->setCellValue("M{$row}", $sensoriRmList ?: '-');
                        $sheet->setCellValue("N{$row}", $detail->duration ?? '-');
                        $sheet->setCellValue("O{$row}", $mixingPaddle);
                        $sheet->setCellValue("P{$row}", $detail->pressure ?? '-');
                        $sheet->setCellValue("Q{$row}", $detail->target_temperature ?? '-');
                        $sheet->setCellValue("R{$row}", $detail->actual_temperature ?? '-');
                        $sheet->setCellValue("S{$row}", $detail->color ?? '-');
                        $sheet->setCellValue("T{$row}", $detail->aroma ?? '-');
                        $sheet->setCellValue("U{$row}", $detail->taste ?? '-');
                        $sheet->setCellValue("V{$row}", $detail->texture ?? '-');
                        $sheet->setCellValue("W{$row}", $detail->notes ?? '-');

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