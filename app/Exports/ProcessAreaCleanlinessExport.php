<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ProcessAreaCleanlinessExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Data Kebersihan Area Proses';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $lastCol = 'U';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI KEBERSIHAN AREA PROSES');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Header (row 4) ─────────────────────────────────────────
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Area',
                    'H' => 'Aktual Suhu Ruang (°C)',
                    'I' => 'Display Suhu Ruang (°C)',
                    // Kebersihan Ruangan
                    'J' => "Kondisi Kebersihan\nRuangan",
                    'K' => 'Catatan',
                    'L' => 'Tindakan Koreksi',
                    'M' => 'Hasil Verifikasi',
                    // Kebersihan Peralatan
                    'N' => "Kondisi Kebersihan\nPeralatan",
                    'O' => 'Catatan',
                    'P' => 'Tindakan Koreksi',
                    'Q' => 'Hasil Verifikasi',
                    // Kebersihan Karyawan
                    'R' => "Kondisi Kebersihan\nKaryawan",
                    'S' => 'Catatan',
                    'T' => 'Tindakan Koreksi',
                    'U' => 'Hasil Verifikasi',
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
                        $itemsByName = $detail->items->keyBy('item');

                        $ruangan   = $itemsByName->get('Kondisi Kebersihan Ruangan');
                        $peralatan = $itemsByName->get('Kondisi Kebersihan Peralatan');
                        $karyawan  = $itemsByName->get('Kondisi Kebersihan Karyawan');
                        $suhu      = $itemsByName->get('Suhu ruang (℃)');

                        $verif = fn($item) => match ((string) ($item?->verification ?? '')) {
                            '1'     => 'OK',
                            '0'     => 'Tidak OK',
                            default => '-',
                        };

                        // Kumpulkan ketidaksesuaian dari semua item yang tidak OK
                        $ketidaksesuaian = collect([$ruangan, $peralatan, $karyawan])
                            ->filter(fn($i) => $i && (string) $i->verification === '0' && $i->notes)
                            ->map(fn($i) => $i->notes)
                            ->implode('; ');

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$row}", $detail->inspection_hour ?? '-');
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", $report->section_name ?? '-');
                        $sheet->setCellValue("H{$row}", $suhu?->temperature_actual ?? '-');
                        $sheet->setCellValue("I{$row}", $suhu?->temperature_display ?? '-');
                        // Kebersihan Ruangan
                        $sheet->setCellValue("J{$row}", $ruangan?->condition ?? '-');
                        $sheet->setCellValue("K{$row}", $ruangan?->notes ?? '-');
                        $sheet->setCellValue("L{$row}", $ruangan?->corrective_action ?? '-');
                        $sheet->setCellValue("M{$row}", $verif($ruangan));
                        // Kebersihan Peralatan
                        $sheet->setCellValue("N{$row}", $peralatan?->condition ?? '-');
                        $sheet->setCellValue("O{$row}", $peralatan?->notes ?? '-');
                        $sheet->setCellValue("P{$row}", $peralatan?->corrective_action ?? '-');
                        $sheet->setCellValue("Q{$row}", $verif($peralatan));
                        // Kebersihan Karyawan
                        $sheet->setCellValue("R{$row}", $karyawan?->condition ?? '-');
                        $sheet->setCellValue("S{$row}", $karyawan?->notes ?? '-');
                        $sheet->setCellValue("T{$row}", $karyawan?->corrective_action ?? '-');
                        $sheet->setCellValue("U{$row}", $verif($karyawan));

                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                            ->getAlignment()->setHorizontal('center')->setWrapText(true);

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