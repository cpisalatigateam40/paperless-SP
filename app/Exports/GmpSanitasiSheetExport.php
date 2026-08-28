<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class GmpSanitasiSheetExport implements WithEvents, WithTitle
{
    public function __construct(
        private $headers,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Sanitasi Area'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // A-G  : No, Tanggal, Shift, Waktu Ke, Jam, Area, Item Verifikasi
                // H    : Std. Klorin
                // I-J  : Sanitasi Area (Kadar Klorin, Suhu)
                // K    : Tindakan Koreksi
                // L    : Keterangan (per item)
                // M    : Catatan (per waktu pemeriksaan)

                $lastCol = 'M';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Verifikasi Sanitasi Area');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Row 4: Group header ────────────────────────────────────
                foreach (['A','B','C','D','E','F','G','H'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }
                $sheet->mergeCells('I4:J4');
                $sheet->setCellValue('I4', 'Sanitasi Area');
                $sheet->mergeCells('K4:K5');
                $sheet->mergeCells('L4:L5');
                $sheet->mergeCells('M4:M5');

                $sheet->getStyle('I4')->getFont()->setBold(true);
                $sheet->getStyle('I4')->getAlignment()
                    ->setHorizontal('center')->setVertical('center');

                $headerInfoLabels = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Waktu Ke',
                    'E' => 'Jam Pemeriksaan',
                    'F' => 'Area',
                    'G' => 'Item Verifikasi',
                    'H' => "Std.\nKlorin",
                    'K' => "Tindakan\nKoreksi",
                    'L' => 'Keterangan',
                    'M' => 'Catatan',
                ];

                foreach ($headerInfoLabels as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                // ── Row 5: Sub-header ──────────────────────────────────────
                $row5 = [
                    'I' => "Kadar Klorin\n(ppm)",
                    'J' => "Suhu\n(°C)",
                ];

                foreach ($row5 as $col => $label) {
                    $sheet->setCellValue("{$col}5", $label);
                    $sheet->getStyle("{$col}5")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}5")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $sheet->getStyle("A4:{$lastCol}5")->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                // ── Data (mulai row 6) ─────────────────────────────────────
                $dataRow = 6;
                $no      = 1;
                $roman   = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

                $reports = $this->headers->filter(fn ($h) => $h->section === 'sanitasi_area')->values();

                foreach ($reports as $report) {
                    foreach ($report->waktuPemeriksaans as $wIndex => $waktu) {
                        foreach ($waktu->sanitationChecks as $san) {
                            $sheet->setCellValue("A{$dataRow}", $no);
                            $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                            $sheet->setCellValue("C{$dataRow}", $report->shift ?? '-');
                            $sheet->setCellValue("D{$dataRow}", $roman[$wIndex] ?? ($wIndex + 1));
                            $sheet->setCellValue("E{$dataRow}", $waktu->jam_pemeriksaan
                                ? Carbon::parse($waktu->jam_pemeriksaan)->format('H:i')
                                : '-');
                            $sheet->setCellValue("F{$dataRow}", $san->section->section_name ?? '-');
                            $sheet->setCellValue("G{$dataRow}", $san->item_verifikasi ?? '-');
                            $sheet->setCellValue("H{$dataRow}", $san->standar_klorin ?? '-');
                            $sheet->setCellValue("I{$dataRow}", $san->kadar_klorin ?? '-');
                            $sheet->setCellValue("J{$dataRow}", $san->suhu ?? '-');
                            $sheet->setCellValue("K{$dataRow}", $san->tindakan_koreksi ?: '-');
                            $sheet->setCellValue("L{$dataRow}", $san->keterangan ?: '-');
                            $sheet->setCellValue("M{$dataRow}", $waktu->catatan ?: '-');

                            $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")
                                ->getAlignment()->setHorizontal('center');
                            $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")->getBorders()
                                ->getAllBorders()->setBorderStyle('thin');

                            $dataRow++;
                            $no++;
                        }
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells("A6:{$lastCol}6");
                    $sheet->setCellValue('A6', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A6')->getFont()->setItalic(true);
                    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
                }

                $allCols = array_merge(array_keys($headerInfoLabels), array_keys($row5));
                foreach ($allCols as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(40);
            },
        ];
    }
}