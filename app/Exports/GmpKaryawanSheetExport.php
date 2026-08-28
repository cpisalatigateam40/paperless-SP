<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class GmpKaryawanSheetExport implements WithEvents, WithTitle
{
    public function __construct(
        private $headers,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'GMP Karyawan'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // A-G  : No, Tanggal, Shift, Waktu Ke, Jam, Area, Nama Karyawan
                // H-O  : Penerapan GMP Karyawan (8 kolom)
                // P    : Tindakan Koreksi
                // Q    : Keterangan

                $lastCol = 'Q';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Verifikasi Penerapan GMP Karyawan');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Row 4: Group header ────────────────────────────────────
                foreach (['A','B','C','D','E','F','G'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }
                $sheet->mergeCells('H4:O4');
                $sheet->setCellValue('H4', 'Penerapan GMP Karyawan');
                $sheet->mergeCells('P4:P5');
                $sheet->mergeCells('Q4:Q5');

                $sheet->getStyle('H4')->getFont()->setBold(true);
                $sheet->getStyle('H4')->getAlignment()
                    ->setHorizontal('center')->setVertical('center');

                $headerInfoLabels = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Waktu Ke',
                    'E' => 'Jam Pemeriksaan',
                    'F' => 'Area',
                    'G' => 'Nama Karyawan',
                    'P' => "Tindakan\nKoreksi",
                    'Q' => 'Keterangan',
                ];

                foreach ($headerInfoLabels as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                // ── Row 5: Sub-header ──────────────────────────────────────
                $row5 = [
                    'H' => "Seragam &\nAPD lengkap",
                    'I' => "Sarung\ntangan utuh",
                    'J' => "Sepatu\nboots bersih",
                    'K' => "Tidak pakai\nperhiasan & jam tangan",
                    'L' => "Kuku & tangan\nbersih, tanpa luka",
                    'M' => "Kuku tidak panjang\n& tidak cat kuku",
                    'N' => "Perilaku &\nkebiasaan kerja",
                    'O' => "Potensi cross\ncontamination",
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

                $checkVal = fn($v) => match(true) {
                    is_null($v) => '-',
                    (bool) $v   => 'Ok',
                    default     => 'Tidak OK',
                };

                $roman = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
                $checkFields = [
                    'seragam_apd_lengkap', 'sarung_tangan_utuh', 'sepatu_boots_bersih',
                    'tidak_pakai_perhiasan', 'kuku_tangan_bersih', 'kuku_tidak_panjang',
                    'perilaku_kerja', 'potensi_cross_contamination',
                ];

                $reports = $this->headers->filter(fn ($h) => $h->section === 'gmp_karyawan')->values();

                foreach ($reports as $report) {
                    foreach ($report->waktuPemeriksaans as $wIndex => $waktu) {
                        foreach ($waktu->employeeChecks as $emp) {
                            $sheet->setCellValue("A{$dataRow}", $no);
                            $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                            $sheet->setCellValue("C{$dataRow}", $report->shift ?? '-');
                            $sheet->setCellValue("D{$dataRow}", $roman[$wIndex] ?? ($wIndex + 1));
                            $sheet->setCellValue("E{$dataRow}", $waktu->jam_pemeriksaan
                                ? Carbon::parse($waktu->jam_pemeriksaan)->format('H:i')
                                : '-');
                            $sheet->setCellValue("F{$dataRow}", $emp->section->section_name ?? '-');
                            $sheet->setCellValue("G{$dataRow}", $emp->employee_name ?? '-');

                            $col = 'H';
                            foreach ($checkFields as $field) {
                                $sheet->setCellValue("{$col}{$dataRow}", $checkVal($emp->$field));
                                $col++;
                            }

                            $sheet->setCellValue("P{$dataRow}", $emp->tindakan_koreksi ?: '-');
                            $sheet->setCellValue("Q{$dataRow}", $waktu->catatan ?: '-');

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