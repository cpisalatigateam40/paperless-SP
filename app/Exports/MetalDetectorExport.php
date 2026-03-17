<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class MetalDetectorExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Data Metal Detector';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ── Judul ──────────────────────────────────────────────────
                $sheet->mergeCells('A1:O1');
                $sheet->setCellValue('A1', 'LAPORAN PEMERIKSAAN METAL DETECTOR');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells('A2:O2');
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Header (row 4) — persis urutan template import ─────────
                // Template: No,Tanggal,Shift,Time,QC,Group,Nama produk,Kode prod,
                //           Speci.Fe,Speci.Non-Fe,Speci.SUS,Hasil verifikasi,
                //           Ketidaksesuaian,Tindakan koreksi,Hasil verif setelah
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Nama Produk',
                    'H' => 'Kode Prod',
                    'I' => 'Speci. Fe 1,5 mm',
                    'J' => 'Speci. Non-Fe 2,0 mm',
                    'K' => 'Speci. SUS 2,5 mm',
                    'L' => 'Hasil Verifikasi',
                    'M' => 'Ketidaksesuaian',
                    'N' => 'Tindakan Koreksi',
                    'O' => 'Hasil Verifikasi Setelah Tindakan Perbaikan',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setWrapText(true);
                }

                // ── Data (mulai row 5) ─────────────────────────────────────
                $row = 5;
                $no  = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$row}", $detail->hour ?? '-');
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", $detail->product->product_name ?? '-');
                        $sheet->setCellValue("H{$row}", $detail->production_code ?? '-');
                        $sheet->setCellValue("I{$row}", $detail->result_fe ?? '-');
                        $sheet->setCellValue("J{$row}", $detail->result_non_fe ?? '-');
                        $sheet->setCellValue("K{$row}", $detail->result_sus316 ?? '-');
                        $sheet->setCellValue("L{$row}", $detail->verif_loma ?? '-');
                        $sheet->setCellValue("M{$row}", $detail->nonconformity ?? '-');
                        $sheet->setCellValue("N{$row}", $detail->corrective_action ?? '-');
                        $sheet->setCellValue("O{$row}", $detail->verif_after_correct ?? '-');

                        $sheet->getStyle("A{$row}:O{$row}")
                            ->getAlignment()->setHorizontal('center');

                        $row++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells('A5:O5');
                    $sheet->setCellValue('A5', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A5')->getFont()->setItalic(true);
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                    $row++;
                }

                // ── Border & auto width ────────────────────────────────────
                $sheet->getStyle("A4:O" . ($row - 1))->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(40);
            },
        ];
    }
}