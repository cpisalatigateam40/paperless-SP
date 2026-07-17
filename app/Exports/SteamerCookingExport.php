<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class SteamerCookingExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Data Steamer Cooking'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'V';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PROSES PEMASAKAN DI STEAMER');
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
                    'D' => 'QC',
                    'E' => 'Nama Produk',
                    'F' => 'Kode Produk',
                    'G' => 'Gramasi',
                    'H' => 'Nomor Steamer',
                    'I' => 'Jumlah Trolly',
                    'J' => 'Tray/Trolly',
                    'K' => 'Waktu Proses Batch',
                    'L' => 'Kode Produksi',
                    'M' => 'Start Process',
                    'N' => 'End Process',
                    'O' => 'Setup Time',
                    'P' => 'Suhu Ruang',
                    'Q' => 'Actual Core Temp',
                    'R' => 'Bentuk',
                    'S' => 'Warna',
                    'T' => 'Aroma',
                    'U' => 'Rasa',
                    'V' => 'Tekstur',
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
                    foreach ($report->batches as $batch) {

                        $waktuProsesBatch = ($batch->start_time ?? '-') . ' - ' . ($batch->end_time ?? '-');

                        foreach ($batch->details as $detail) {

                            $coreTempText = $detail->coreTemps
                                ->pluck('temp_value')
                                ->implode(', ');

                            $sheet->setCellValue("A{$row}", $no);
                            $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                            $sheet->setCellValue("C{$row}", $report->shift ?? '-');
                            $sheet->setCellValue("D{$row}", $report->creator->name ?? $report->created_by ?? '-');
                            $sheet->setCellValue("E{$row}", $report->product->product_name ?? '-');
                            $sheet->setCellValue("F{$row}", $report->product_code_range ?? '-');
                            $sheet->setCellValue("G{$row}", $report->gramase ?? '-');
                            $sheet->setCellValue("H{$row}", $batch->steamer_number ?? '-');
                            $sheet->setCellValue("I{$row}", $batch->trolley_count ?? '-');
                            $sheet->setCellValue("J{$row}", $batch->tray_per_trolley ?? '-');
                            $sheet->setCellValue("K{$row}", $waktuProsesBatch);
                            $sheet->setCellValue("L{$row}", $detail->production_code ?? '-');
                            $sheet->setCellValue("M{$row}", $detail->start_process ?? '-');
                            $sheet->setCellValue("N{$row}", $detail->end_process ?? '-');
                            $sheet->setCellValue("O{$row}", $detail->setup_time ?? '-');
                            $sheet->setCellValue("P{$row}", $detail->room_temp ?? '-');
                            $sheet->setCellValue("Q{$row}", $coreTempText ?: '-');
                            $sheet->setCellValue("R{$row}", $detail->sensory_bentuk ?? '-');
                            $sheet->setCellValue("S{$row}", $detail->sensory_warna ?? '-');
                            $sheet->setCellValue("T{$row}", $detail->sensory_aroma ?? '-');
                            $sheet->setCellValue("U{$row}", $detail->sensory_rasa ?? '-');
                            $sheet->setCellValue("V{$row}", $detail->sensory_tekstur ?? '-');

                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                                ->getAlignment()->setHorizontal('center')->setWrapText(true);

                            $row++;
                            $no++;
                        }
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