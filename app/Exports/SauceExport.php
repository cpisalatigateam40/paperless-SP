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
                $lastCol = 'AG';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Verifikasi Proses Pemasakan di Steam Kettle');
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
                    'I' => 'Formula',
                    'J' => 'Waktu Start',
                    'K' => 'Waktu Stop',
                    'L' => 'Bahan',
                    'M' => 'Berat',
                    'N' => 'Status',
                    'O' => 'Tindakan Koreksi RM',
                    'P' => 'Keterangan RM',
                    'Q' => 'Lama Proses',
                    'R' => 'Nomor Mesin',
                    'S' => 'Mixing Paddle',
                    'T' => 'Hasil Brix (%)',
                    'U' => 'Hasil Salinity (%)',
                    'V' => 'Pressure',
                    'W' => 'Target Temp. (°C)',
                    'X' => 'Aktual Temp. (°C)',
                    'Y' => 'Kenampakan',
                    'Z' => 'Sensori Warna',
                    'AA' => 'Sensori Aroma',
                    'AB' => 'Sensori Rasa',
                    'AC' => 'Sensori Tekstur',
                    'AD' => 'Status Produk',
                    'AE' => 'Tindakan Perbaikan',
                    'AF' => 'Catatan',
                    'AG' => 'Catatan & Dokumentasi',
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

                        $correctiveRmList = $detail->rawMaterials
                            ->map(fn($rm) => $rm->corrective_action ?? '-')
                            ->implode(', ');

                        $keteranganRmList = $detail->rawMaterials
                            ->map(fn($rm) => $rm->keterangan ?? '-')
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
                        $sheet->setCellValue(
                            "G{$row}",
                            trim(($report->product->product_name ?? '-') . ' - ' . ($report->gramase ?? '-'))
                        );
                        $sheet->setCellValue("H{$row}", $report->production_code ?? '-');
                        $sheet->setCellValue("I{$row}", $report->formula->formula_name ?? '-');
                        $sheet->setCellValue("J{$row}", $report->start_time ?? '-');
                        $sheet->setCellValue("K{$row}", $report->end_time ?? '-');
                        $sheet->setCellValue("L{$row}", $bahanList ?: '-');
                        $sheet->setCellValue("M{$row}", $beratList ?: '-');
                        $sheet->setCellValue("N{$row}", $sensoriRmList ?: '-');
                        $sheet->setCellValue("O{$row}", $correctiveRmList ?: '-');
                        $sheet->setCellValue("P{$row}", $keteranganRmList ?: '-');
                        $sheet->setCellValue("Q{$row}", $detail->duration ?? '-');
                        $sheet->setCellValue("R{$row}", $detail->no_mesin ?? '-');
                        $sheet->setCellValue("S{$row}", $mixingPaddle);
                        $sheet->setCellValue("T{$row}", $detail->brix ?? '-');
                        $sheet->setCellValue("U{$row}", $detail->salinity ?? '-');
                        $sheet->setCellValue("V{$row}", $detail->pressure ?? '-');
                        $sheet->setCellValue("W{$row}", $detail->target_temperature ?? '-');
                        $sheet->setCellValue("X{$row}", $detail->actual_temperature ?? '-');
                        $sheet->setCellValue("Y{$row}", $detail->appearance ?? '-');
                        $sheet->setCellValue("Z{$row}", $detail->color ?? '-');
                        $sheet->setCellValue("AA{$row}", $detail->aroma ?? '-');
                        $sheet->setCellValue("AB{$row}", $detail->taste ?? '-');
                        $sheet->setCellValue("AC{$row}", $detail->texture ?? '-');
                        $sheet->setCellValue("AD{$row}", $detail->product_status ?? '-');
                        $sheet->setCellValue("AE{$row}", $detail->corrective_action ?? '-');
                        $sheet->setCellValue("AF{$row}", $detail->notes ?? '-');
                        $sheet->setCellValue("AG{$row}", $report->documentation_notes ?? '-');

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