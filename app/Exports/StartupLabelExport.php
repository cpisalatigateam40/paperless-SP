<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StartupLabelExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Startup Label';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // ==================================================
                // JUDUL
                // ==================================================
                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue(
                    'A1',
                    'LAPORAN PEMERIKSAAN STARTUP LABEL'
                );

                $sheet->getStyle('A1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(13);

                $sheet->getStyle('A1')
                    ->getAlignment()
                    ->setHorizontal('center');

                // ==================================================
                // PERIODE
                // ==================================================
                $sheet->mergeCells('A2:K2');
                $sheet->setCellValue(
                    'A2',
                    'Periode: ' . $this->periodLabel
                );

                $sheet->getStyle('A2')
                    ->getAlignment()
                    ->setHorizontal('center');

                // ==================================================
                // HEADER
                // ==================================================
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Produk',
                    'E' => 'Packaging',
                    'F' => 'Jam',
                    'G' => 'Kode Produksi',
                    'H' => 'Best Before',
                    'I' => 'Hasil',
                    'J' => 'Tindakan Koreksi',
                    'K' => 'Verifikasi',
                ];

                foreach ($headers as $col => $label) {

                    $sheet->setCellValue(
                        "{$col}4",
                        $label
                    );

                    $sheet->getStyle("{$col}4")
                        ->getFont()
                        ->setBold(true);

                    $sheet->getStyle("{$col}4")
                        ->getAlignment()
                        ->setHorizontal('center')
                        ->setVertical('center')
                        ->setWrapText(true);
                }

                $row = 5;
                $no = 1;

                // ==================================================
                // DATA
                // ==================================================
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
                            $detail->packaging ?? '-'
                        );

                        $sheet->setCellValue(
                            "F{$row}",
                            $detail->time ?? '-'
                        );

                        $sheet->setCellValue(
                            "G{$row}",
                            $detail->production_code ?? '-'
                        );

                        $sheet->setCellValue(
                            "H{$row}",
                            $detail->best_before
                                ? Carbon::parse($detail->best_before)
                                    ->format('d/m/Y')
                                : '-'
                        );

                        $sheet->setCellValue(
                            "I{$row}",
                            $detail->result ?? '-'
                        );

                        $sheet->setCellValue(
                            "J{$row}",
                            $detail->corrective_action ?? '-'
                        );

                        $sheet->setCellValue(
                            "K{$row}",
                            $report->approved_by ?? '-'
                        );

                        $sheet->getStyle("A{$row}:K{$row}")
                            ->getAlignment()
                            ->setHorizontal('center')
                            ->setVertical('center');

                        $row++;
                        $no++;
                    }
                }

                // ==================================================
                // JIKA TIDAK ADA DATA
                // ==================================================
                if ($no === 1) {

                    $sheet->mergeCells('A5:K5');

                    $sheet->setCellValue(
                        'A5',
                        'Tidak ada data untuk periode yang dipilih.'
                    );

                    $sheet->getStyle('A5')
                        ->getFont()
                        ->setItalic(true);

                    $sheet->getStyle('A5')
                        ->getAlignment()
                        ->setHorizontal('center');

                    $row++;
                }

                // ==================================================
                // BORDER
                // ==================================================
                $sheet->getStyle(
                    'A4:K' . ($row - 1)
                )->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // ==================================================
                // AUTO WIDTH
                // ==================================================
                foreach (array_keys($headers) as $col) {

                    $sheet->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                // ==================================================
                // HEADER HEIGHT
                // ==================================================
                $sheet->getRowDimension(4)
                    ->setRowHeight(30);
            },
        ];
    }
}