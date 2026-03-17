<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ThawingExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Data Thawing';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ── Judul ──────────────────────────────────────────────────
                $sheet->mergeCells('A1:M1');
                $sheet->setCellValue('A1', 'PEMERIKSAAN PROSES THAWING');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal('center');

                $sheet->mergeCells('A2:M2');
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A2')->getFont()->setSize(10);

                // ── Header tabel (row 4) ────────────────────────────────────
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Waktu Thawing Awal',
                    'E' => 'Waktu Thawing Akhir',
                    'F' => 'Kondisi Awal Kemasan RM',
                    'G' => 'Nama Bahan Baku',
                    'H' => 'Kode Produksi',
                    'I' => 'Jumlah',
                    'J' => 'Kondisi Ruang',
                    'K' => 'Waktu Pemeriksaan',
                    'L' => 'Suhu Ruang (°C)',
                    'M' => 'Suhu Air Thawing (°C)',
                    'N' => 'Suhu Produk (°C)',
                    'O' => 'Kondisi Produk',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')
                        ->setWrapText(true);
                }

                // Merge judul ulang sesuai jumlah kolom
                $lastCol = array_key_last($headers);
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");

                // ── Data (mulai row 5) ────────────────────────────────────
                $row = 5;
                $no  = 1;

                foreach ($this->reports as $report) {
                    foreach ($report->details as $detail) {
                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", $report->date
                            ? Carbon::parse($report->date)->format('d-m-Y') : '-');
                        $sheet->setCellValue("C{$row}", $report->shift ?? '-');
                        $sheet->setCellValue("D{$row}", $detail->start_thawing_time ?? '-');
                        $sheet->setCellValue("E{$row}", $detail->end_thawing_time ?? '-');
                        $sheet->setCellValue("F{$row}", ucfirst($detail->package_condition ?? '-'));
                        $sheet->setCellValue("G{$row}", $detail->rawMaterial->material_name ?? '-');
                        $sheet->setCellValue("H{$row}", $detail->production_code ?? '-');
                        $sheet->setCellValue("I{$row}", $detail->qty ?? '-');
                        $sheet->setCellValue("J{$row}", ucfirst($detail->room_condition ?? '-'));
                        $sheet->setCellValue("K{$row}", $detail->inspection_time ?? '-');
                        $sheet->setCellValue("L{$row}", $detail->room_temp ?? '-');
                        $sheet->setCellValue("M{$row}", $detail->water_temp ?? '-');
                        $sheet->setCellValue("N{$row}", $detail->product_temp ?? '-');
                        $sheet->setCellValue("O{$row}", ucfirst($detail->product_condition ?? '-'));

                        $sheet->getStyle("A{$row}:O{$row}")
                            ->getAlignment()->setHorizontal('center');

                        $row++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells("A5:O5");
                    $sheet->setCellValue('A5', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                    $sheet->getStyle('A5')->getFont()->setItalic(true);
                    $row++;
                }

                // ── Border seluruh tabel ───────────────────────────────────
                $lastRow = $row - 1;
                $sheet->getStyle("A4:O{$lastRow}")->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');

                // ── Auto width kolom ──────────────────────────────────────
                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // ── Row height header ─────────────────────────────────────
                $sheet->getRowDimension(4)->setRowHeight(30);
            },
        ];
    }
}