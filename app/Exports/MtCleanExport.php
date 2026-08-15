<?php

namespace App\Exports;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class MtCleanExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'MT Clean';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                /*
                |--------------------------------------------------------------------------
                | JUDUL
                |--------------------------------------------------------------------------
                */
                $sheet->mergeCells('A1:M1');

                $sheet->setCellValue(
                    'A1',
                    'LAPORAN PEMERIKSAAN MT CLEAN'
                );

                $sheet->getStyle('A1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(13);

                $sheet->getStyle('A1')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                /*
                |--------------------------------------------------------------------------
                | PERIODE
                |--------------------------------------------------------------------------
                */
                $sheet->mergeCells('A2:M2');

                $sheet->setCellValue(
                    'A2',
                    'Periode: ' . $this->periodLabel
                );

                $sheet->getStyle('A2')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                /*
                |--------------------------------------------------------------------------
                | HEADER
                |--------------------------------------------------------------------------
                */
                $sheet->mergeCells('A4:A5'); // No
                $sheet->mergeCells('B4:B5'); // Tanggal
                $sheet->mergeCells('C4:C5'); // Shift
                $sheet->mergeCells('D4:D5'); // Produk
                $sheet->mergeCells('E4:E5'); // Jam
                $sheet->mergeCells('F4:F5'); // MT I
                $sheet->mergeCells('G4:G5'); // MT II
                $sheet->mergeCells('H4:H5'); // Berat Tangkapan Logam
                $sheet->mergeCells('I4:I5'); // Temuan
                $sheet->mergeCells('J4:K4'); // Kondisi
                $sheet->mergeCells('L4:L5'); // Keterangan
                $sheet->mergeCells('M4:M5'); // Koreksi

                $sheet->setCellValue('A4', 'No');
                $sheet->setCellValue('B4', 'Tanggal');
                $sheet->setCellValue('C4', 'Shift');
                $sheet->setCellValue('D4', 'Nama Produk');
                $sheet->setCellValue('E4', 'Jam');
                $sheet->setCellValue('F4', 'Magnet Trap I');
                $sheet->setCellValue('G4', 'Magnet Trap II');
                $sheet->setCellValue('H4', 'Berat Tangkapan Logam (gr)');
                $sheet->setCellValue('I4', 'Jenis Temuan');

                $sheet->setCellValue('J4', 'Kondisi');
                $sheet->setCellValue('J5', 'Bersih');
                $sheet->setCellValue('K5', 'Tidak Bersih');

                $sheet->setCellValue('L4', 'Keterangan');
                $sheet->setCellValue('M4', 'Tindakan Koreksi');

                /*
                |--------------------------------------------------------------------------
                | STYLE HEADER
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle('A4:M5')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A4:M5')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle('A4:M5')
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet->getStyle('A4:M5')
                    ->getAlignment()
                    ->setWrapText(true);

                /*
                |--------------------------------------------------------------------------
                | DATA
                |--------------------------------------------------------------------------
                */
                $row = 6;
                $no = 1;

                foreach ($this->reports as $report) {

                    foreach ($report->details as $detail) {

                        $sheet->setCellValue(
                            "A{$row}",
                            $no
                        );

                        $sheet->setCellValue(
                            "B{$row}",
                            $report->date
                                ? Carbon::parse($report->date)
                                    ->format('d/m/Y')
                                : '-'
                        );

                        $sheet->setCellValue(
                            "C{$row}",
                            $report->shift ?? '-'
                        );

                        $sheet->setCellValue(
                            "D{$row}",
                            $detail->product->product_name ?? '-'
                        );

                        $sheet->setCellValue(
                            "E{$row}",
                            $detail->time
                                ? substr($detail->time, 0, 5)
                                : '-'
                        );

                        $sheet->setCellValue(
                            "F{$row}",
                            $detail->mt_1 ?? '-'
                        );

                        $sheet->setCellValue(
                            "G{$row}",
                            $detail->mt_2 ?? '-'
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | BERAT TANGKAPAN LOGAM
                        |--------------------------------------------------------------------------
                        */
                        $sheet->setCellValue(
                            "H{$row}",
                            $detail->metal_weight !== null
                                ? $detail->metal_weight
                                : '-'
                        );

                        $sheet->setCellValue(
                            "I{$row}",
                            $detail->finding_type ?? '-'
                        );

                        $sheet->setCellValue(
                            "J{$row}",
                            $detail->condition == 'Bersih'
                                ? '✓'
                                : ''
                        );

                        $sheet->setCellValue(
                            "K{$row}",
                            $detail->condition == 'Tidak Bersih'
                                ? '✓'
                                : ''
                        );

                        $sheet->setCellValue(
                            "L{$row}",
                            $detail->note ?? '-'
                        );

                        $sheet->setCellValue(
                            "M{$row}",
                            $detail->corrective_action ?? '-'
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | ALIGNMENT DATA
                        |--------------------------------------------------------------------------
                        */
                        $sheet->getStyle(
                            "A{$row}:M{$row}"
                        )->getAlignment()
                            ->setVertical(
                                Alignment::VERTICAL_CENTER
                            );

                        $row++;
                        $no++;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | JIKA TIDAK ADA DATA
                |--------------------------------------------------------------------------
                */
                if ($no === 1) {

                    $sheet->mergeCells('A6:M6');

                    $sheet->setCellValue(
                        'A6',
                        'Tidak ada data untuk periode yang dipilih.'
                    );

                    $sheet->getStyle('A6')
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    $sheet->getStyle('A6')
                        ->getFont()
                        ->setItalic(true);

                    $row++;
                }

                /*
                |--------------------------------------------------------------------------
                | BORDER
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle(
                    "A4:M" . ($row - 1)
                )->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        Border::BORDER_THIN
                    );

                /*
                |--------------------------------------------------------------------------
                | WRAP TEXT
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle(
                    "A4:M" . ($row - 1)
                )->getAlignment()
                    ->setWrapText(true);

                /*
                |--------------------------------------------------------------------------
                | CENTER ALIGNMENT
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle(
                    "A6:C" . ($row - 1)
                )->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle(
                    "E6:H" . ($row - 1)
                )->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle(
                    "J6:K" . ($row - 1)
                )->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                /*
                |--------------------------------------------------------------------------
                | AUTO WIDTH
                |--------------------------------------------------------------------------
                */
                foreach (range('A', 'M') as $col) {
                    $sheet->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                /*
                |--------------------------------------------------------------------------
                | ROW HEIGHT
                |--------------------------------------------------------------------------
                */
                $sheet->getRowDimension(4)
                    ->setRowHeight(30);

                $sheet->getRowDimension(5)
                    ->setRowHeight(30);
            }
        ];
    }
}