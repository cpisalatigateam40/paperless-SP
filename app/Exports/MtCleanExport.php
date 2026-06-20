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
                $sheet->mergeCells('A1:L1');
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
                $sheet->mergeCells('A2:L2');

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
                $sheet->mergeCells('H4:H5'); // Temuan
                $sheet->mergeCells('I4:J4'); // Kondisi
                $sheet->mergeCells('K4:K5'); // Keterangan
                $sheet->mergeCells('L4:L5'); // Koreksi

                $sheet->setCellValue('A4', 'No');
                $sheet->setCellValue('B4', 'Tanggal');
                $sheet->setCellValue('C4', 'Shift');
                $sheet->setCellValue('D4', 'Nama Produk');
                $sheet->setCellValue('E4', 'Jam');
                $sheet->setCellValue('F4', 'Magnet Trap I');
                $sheet->setCellValue('G4', 'Magnet Trap II');
                $sheet->setCellValue('H4', 'Jenis Temuan');
                $sheet->setCellValue('I4', 'Kondisi');
                $sheet->setCellValue('I5', 'Bersih');
                $sheet->setCellValue('J5', 'Tidak Bersih');
                $sheet->setCellValue('K4', 'Keterangan');
                $sheet->setCellValue('L4', 'Tindakan Koreksi');

                $sheet->getStyle('A4:L5')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A4:L5')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle('A4:L5')
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet->getStyle('A4:L5')
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

                        $sheet->setCellValue(
                            "H{$row}",
                            $detail->finding_type ?? '-'
                        );

                        $sheet->setCellValue(
                            "I{$row}",
                            $detail->condition == 'Bersih'
                                ? '✓'
                                : ''
                        );

                        $sheet->setCellValue(
                            "J{$row}",
                            $detail->condition == 'Tidak Bersih'
                                ? '✓'
                                : ''
                        );

                        $sheet->setCellValue(
                            "K{$row}",
                            $detail->note ?? '-'
                        );

                        $sheet->setCellValue(
                            "L{$row}",
                            $detail->corrective_action ?? '-'
                        );

                        $sheet->getStyle(
                            "A{$row}:L{$row}"
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

                    $sheet->mergeCells('A6:L6');

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
                    "A4:L" . ($row - 1)
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
                    "A4:L" . ($row - 1)
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
                    "I6:J" . ($row - 1)
                )->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                /*
                |--------------------------------------------------------------------------
                | AUTO WIDTH
                |--------------------------------------------------------------------------
                */
                foreach (range('A', 'L') as $col) {
                    $sheet->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                /*
                |--------------------------------------------------------------------------
                | ROW HEIGHT
                |--------------------------------------------------------------------------
                */
                $sheet->getRowDimension(4)
                    ->setRowHeight(25);

                $sheet->getRowDimension(5)
                    ->setRowHeight(25);
            }
        ];
    }
}