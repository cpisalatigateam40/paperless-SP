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
                $sheet->mergeCells('A1:W1');
                $sheet->setCellValue('A1', 'Verifikasi Kondisi Ruang Penyimpanan Bahan Baku dan Bahan Penunjang');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells('A2:W2');
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Header (row 4) ─────────────────────────────────────────
                // Kolom mengikuti struktur di form: setiap item punya
                // Kondisi, Catatan, Tindakan Koreksi, Hasil Verifikasi
                // (termasuk item "Suhu Ruang (°C)", tanpa split kolom suhu/RH terpisah)
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Nama Ruangan',
                    'H' => "Kondisi &\nPenempatan Barang",
                    'I' => 'Catatan',
                    'J' => 'Tindakan Koreksi',
                    'K' => 'Hasil Verifikasi',
                    'L' => 'Pelabelan',
                    'M' => 'Catatan',
                    'N' => 'Tindakan Koreksi',
                    'O' => 'Hasil Verifikasi',
                    'P' => 'Kebersihan Ruangan',
                    'Q' => 'Catatan',
                    'R' => 'Tindakan Koreksi',
                    'S' => 'Hasil Verifikasi',
                    'T' => 'Suhu Ruang (°C)',
                    'U' => 'Catatan',
                    'V' => 'Tindakan Koreksi',
                    'W' => 'Hasil Verifikasi',
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

                        $kondisi = $itemsByName->get('Kondisi dan penempatan barang');
                        $label_  = $itemsByName->get('Pelabelan');
                        $bersih  = $itemsByName->get('Kebersihan Ruangan');
                        $suhu    = $itemsByName->get('Suhu ruang (℃) / RH (%)');

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
                        $sheet->setCellValue("G{$row}", $report->room_name ?? '-');
                        $sheet->setCellValue("H{$row}", $kondisi?->condition ?? '-');
                        $sheet->setCellValue("I{$row}", $notes($kondisi));
                        $sheet->setCellValue("J{$row}", $kondisi?->corrective_action ?? '-');
                        $sheet->setCellValue("K{$row}", $verif($kondisi));
                        $sheet->setCellValue("L{$row}", $label_?->condition ?? '-');
                        $sheet->setCellValue("M{$row}", $notes($label_));
                        $sheet->setCellValue("N{$row}", $label_?->corrective_action ?? '-');
                        $sheet->setCellValue("O{$row}", $verif($label_));
                        $sheet->setCellValue("P{$row}", $bersih?->condition ?? '-');
                        $sheet->setCellValue("Q{$row}", $notes($bersih));
                        $sheet->setCellValue("R{$row}", $bersih?->corrective_action ?? '-');
                        $sheet->setCellValue("S{$row}", $verif($bersih));
                        // Suhu ruang: tampilkan apa adanya (mendukung nilai negatif, mis. "Suhu: -1.2 °C")
                        $sheet->setCellValue("T{$row}", $suhu?->condition ?? '-');
                        $sheet->setCellValue("U{$row}", $notes($suhu));
                        $sheet->setCellValue("V{$row}", $suhu?->corrective_action ?? '-');
                        $sheet->setCellValue("W{$row}", $verif($suhu));

                        $sheet->getStyle("A{$row}:W{$row}")
                            ->getAlignment()->setHorizontal('center')->setWrapText(true);

                        $row++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells('A5:W5');
                    $sheet->setCellValue('A5', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A5')->getFont()->setItalic(true);
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                    $row++;
                }

                // ── Border & auto width ────────────────────────────────────
                $sheet->getStyle("A4:W" . ($row - 1))->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(40);
            },
        ];
    }
}