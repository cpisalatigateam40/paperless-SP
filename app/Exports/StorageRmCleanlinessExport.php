<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class StorageRmCleanlinessExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Data Kebersihan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ── Judul ──────────────────────────────────────────────────
                $sheet->mergeCells('A1:X1');
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI KEBERSIHAN RUANG PENYIMPANAN RM');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells('A2:X2');
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Header (row 4) ─────────────────────────────────────────
                // Kolom: no, tanggal, shift, time, qc, group,
                //        suhu ruang, rh ruang,
                //        [kondisi & penempatan] kondisi, catatan, tindakan koreksi, verifikasi,
                //        [pelabelan]            kondisi, catatan, tindakan koreksi, verifikasi,
                //        [kebersihan ruangan]   kondisi, catatan, tindakan koreksi, verifikasi,
                //        ketidaksesuaian (notes suhu), tindakan koreksi suhu, verifikasi suhu
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Nama Ruangan',  // ← tambah
                    'H' => 'Suhu Ruang (°C)',
                    'I' => 'RH Ruang (%)',
                    'J' => "Kondisi &\nPenempatan Barang",
                    'K' => 'Catatan',
                    'L' => 'Tindakan Koreksi',
                    'M' => 'Hasil Verifikasi',
                    'N' => 'Pelabelan',
                    'O' => 'Catatan',
                    'P' => 'Tindakan Koreksi',
                    'Q' => 'Hasil Verifikasi',
                    'R' => 'Kebersihan Ruangan',
                    'S' => 'Catatan',
                    'T' => 'Tindakan Koreksi',
                    'U' => 'Hasil Verifikasi',
                    'V' => 'Ketidaksesuaian',
                    'W' => 'Tindakan Koreksi',
                    'X' => 'Hasil Verifikasi',
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
                        // Kelompokkan items berdasarkan nama item
                        $itemsByName = $detail->items->keyBy('item');

                        $kondisi  = $itemsByName->get('Kondisi dan penempatan barang');
                        $label_   = $itemsByName->get('Pelabelan');
                        $bersih   = $itemsByName->get('Kebersihan Ruangan');
                        $suhu     = $itemsByName->get('Suhu ruang (℃) / RH (%)');

                        // Parse suhu & RH dari kondisi (format: "Suhu: X °C, RH: Y %")
                        $suhuVal = '-';
                        $rhVal   = '-';
                        if ($suhu && $suhu->condition) {
                            preg_match('/Suhu:\s*([\d.]+)/', $suhu->condition, $mS);
                            preg_match('/RH:\s*([\d.]+)/', $suhu->condition, $mR);
                            $suhuVal = $mS[1] ?? '-';
                            $rhVal   = $mR[1] ?? '-';
                        }

                        $verif = fn($item) => match((string)($item?->verification ?? '')) {
                            '1'  => 'OK',
                            '0'  => 'Tidak OK',
                            default => '-',
                        };

                        $notes = fn($item) => $item
                            ? (is_string($item->notes) && str_starts_with($item->notes, '[')
                                ? implode(', ', json_decode($item->notes, true) ?? [])
                                : ($item->notes ?? '-'))
                            : '-';

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$row}", $detail->inspection_hour ?? '-');
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", $report->room_name ?? '-');  // ← tambah
                        $sheet->setCellValue("H{$row}", $suhuVal);
                        $sheet->setCellValue("I{$row}", $rhVal);
                        $sheet->setCellValue("J{$row}", $kondisi?->condition ?? '-');
                        $sheet->setCellValue("K{$row}", $notes($kondisi));
                        $sheet->setCellValue("L{$row}", $kondisi?->corrective_action ?? '-');
                        $sheet->setCellValue("M{$row}", $verif($kondisi));
                        $sheet->setCellValue("N{$row}", $label_?->condition ?? '-');
                        $sheet->setCellValue("O{$row}", $notes($label_));
                        $sheet->setCellValue("P{$row}", $label_?->corrective_action ?? '-');
                        $sheet->setCellValue("Q{$row}", $verif($label_));
                        $sheet->setCellValue("R{$row}", $bersih?->condition ?? '-');
                        $sheet->setCellValue("S{$row}", $notes($bersih));
                        $sheet->setCellValue("T{$row}", $bersih?->corrective_action ?? '-');
                        $sheet->setCellValue("U{$row}", $verif($bersih));
                        $sheet->setCellValue("V{$row}", $suhu?->notes ?? '-');
                        $sheet->setCellValue("W{$row}", $suhu?->corrective_action ?? '-');
                        $sheet->setCellValue("X{$row}", $verif($suhu));

                        $sheet->getStyle("A{$row}:X{$row}")
                            ->getAlignment()->setHorizontal('center')->setWrapText(true);

                        $row++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells('A5:X5');
                    $sheet->setCellValue('A5', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A5')->getFont()->setItalic(true);
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                    $row++;
                }

                // ── Border & auto width ────────────────────────────────────
                $sheet->getStyle("A4:X" . ($row - 1))->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(40);
            },
        ];
    }
}