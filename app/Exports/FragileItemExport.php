<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class FragileItemExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Barang Mudah Pecah'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'J';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI BARANG MUDAH PECAH');
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
                    'G' => 'Area (Section)',   // section_name dari fragile item
                    'H' => 'Nama Barang',
                    'I' => 'Pemilik (Area)',
                    'J' => 'Jumlah',
                    // time_start, time_end disimpan sebagai 0/1 (checkbox),
                    // keterangan dari notes (0/1) juga
                    // Sesuai gambar: Keterangan kolom terakhir
                    // Tambah kolom waktu awal, waktu akhir, keterangan
                ];

                // Sesuai gambar ada: No,Tanggal,Shift,Time,QC,Shift,Nama Barang(section+nama),Pemilik,Jumlah,Keterangan
                // Time = waktu awal (time_start) & waktu akhir (time_end) digabung
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Area (Section)',
                    'H' => 'Nama Barang',
                    'I' => 'Pemilik (Area)',
                    'J' => 'Jumlah',
                    'K' => 'Waktu Awal',
                    'L' => 'Waktu Akhir',
                    'M' => 'Keterangan',
                ];
                $lastCol = 'M';

                // Update merge judul
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");

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

                    // Kelompokkan details per section_name item
                    $detailsBySection = $report->details
                        ->filter(fn($d) => $d->item !== null)
                        ->groupBy(fn($d) => $d->item->section_name ?? 'Lainnya');

                    foreach ($detailsBySection as $sectionName => $details) {
                        foreach ($details as $detail) {
                            $fragile = $detail->item;

                            // time_start & time_end disimpan 0/1 (checkbox)
                            $timeStart = $detail->time_start == '1' ? '✓' : '-';
                            $timeEnd   = $detail->time_end   == '1' ? '✓' : '-';
                            $notes     = $detail->notes      == '1' ? '✓' : '-';

                            $sheet->setCellValue("A{$row}", $no);
                            $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                            $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                            $sheet->setCellValue("D{$row}", Carbon::parse($report->created_at)->format('H:i'));
                            $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                            $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                            $sheet->setCellValue("G{$row}", $sectionName);
                            $sheet->setCellValue("H{$row}", $fragile?->item_name ?? '-');
                            $sheet->setCellValue("I{$row}", $fragile?->owner ?? '-');
                            $sheet->setCellValue("J{$row}", $fragile?->quantity ?? '-');
                            $sheet->setCellValue("K{$row}", $timeStart);
                            $sheet->setCellValue("L{$row}", $timeEnd);
                            $sheet->setCellValue("M{$row}", $notes);

                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                                ->getAlignment()->setHorizontal('center');

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

                $sheet->getRowDimension(4)->setRowHeight(30);
            },
        ];
    }
}