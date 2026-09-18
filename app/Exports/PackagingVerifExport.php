<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class PackagingVerifExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Verifikasi Kemasan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Sekarang sampai kolom X karena Production Code ditambahkan
                $lastCol = 'X';

                // =========================
                // JUDUL
                // =========================
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Verifikasi Proses Pengemasan');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // =========================
                // PERIODE
                // =========================
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // =========================
                // HEADER
                // =========================
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Nama Produk',
                    'H' => 'Production Code',
                    'I' => 'In Cutting',
                    'J' => 'Pengemasan',
                    'K' => 'Jumlah Sampling',
                    'L' => 'Hasil Sampling',
                    'M' => "Kondisi Seal\n(1-5)",
                    'N' => "Vacuum\n(1-5)",
                    'O' => "Isi Per Pack\n(1-5)",
                    'P' => "Std Panjang/pcs",
                    'Q' => "Aktual Panjang/pcs\n(1-5)",
                    'R' => "Rata-rata Panjang/pcs",
                    'S' => "Std Berat/pcs",
                    'T' => "Aktual Berat/pcs\n(1-5)",
                    'U' => "Rata-rata Berat/pcs",
                    'V' => "Std Berat/pack",
                    'W' => "Aktual Berat/pack\n(1-5)",
                    'X' => "Rata-rata Berat/pack",
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);

                    $sheet->getStyle("{$col}4")
                        ->getFont()
                        ->setBold(true);

                    $sheet->getStyle("{$col}4")
                        ->getAlignment()
                        ->setHorizontal('center')
                        ->setVertical('center')
                        ->setWrapText(true);
                }

                // =========================
                // DATA
                // =========================
                $row = 5;
                $no = 1;

                foreach ($this->reports as $report) {

                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2),
                        2,
                        ''
                    );

                    foreach ($report->details as $detail) {

                        $cl = $detail->checklist;

                        // =========================
                        // Helper actual 1-5
                        // =========================
                        $join = fn(string $prefix) => collect(range(1, 5))
                            ->map(fn($i) => $cl?->{"{$prefix}_{$i}"} ?? null)
                            ->filter(fn($v) => $v !== null && $v !== '')
                            ->implode(', ');

                        // =========================
                        // Content Per Pack
                        // =========================
                        $joinContentPerPack = function () use ($cl): string {

                            // Coba dari JSON terlebih dahulu
                            $json = $cl?->content_per_pack_json;

                            $values = is_array($json)
                                ? $json
                                : json_decode($json ?? '[]', true);

                            // Fallback ke kolom lama jika JSON kosong
                            if (empty($values)) {
                                $values = collect(range(1, 5))
                                    ->map(
                                        fn($i) =>
                                        $cl?->{"content_per_pack_{$i}"} ?? null
                                    )
                                    ->filter(
                                        fn($v) =>
                                        $v !== null && $v !== ''
                                    )
                                    ->values()
                                    ->toArray();
                            }

                            return collect($values)
                                ->filter(
                                    fn($v) =>
                                    $v !== null && $v !== ''
                                )
                                ->implode(', ');
                        };

                        // =========================
                        // In Cutting
                        // =========================
                        $inCutting = $cl?->in_cutting_manual_1
                            ? 'Manual'
                            : (
                                $cl?->in_cutting_machine_1
                                    ? 'Mesin'
                                    : '-'
                            );

                        // =========================
                        // Packaging
                        // =========================
                        $packaging = $cl?->packaging_thermoformer_1
                            ? 'Thermoformer'
                            : (
                                $cl?->packaging_manual_1
                                    ? 'Manual'
                                    : '-'
                            );

                        // =========================
                        // DATA EXCEL
                        // =========================

                        $sheet->setCellValue(
                            "A{$row}",
                            $no
                        );

                        $sheet->setCellValue(
                            "B{$row}",
                            Carbon::parse($report->date)->format('d/m/Y')
                        );

                        $sheet->setCellValue(
                            "C{$row}",
                            $shiftNum ?: ($report->shift ?? '-')
                        );

                        $sheet->setCellValue(
                            "D{$row}",
                            $detail->time ?? '-'
                        );

                        $sheet->setCellValue(
                            "E{$row}",
                            $report->created_by ?? '-'
                        );

                        $sheet->setCellValue(
                            "F{$row}",
                            $shiftGroup ?: '-'
                        );

                        // Nama Produk + Gramase
                        $sheet->setCellValue(
                            "G{$row}",
                            trim(
                                ($detail->product->product_name ?? '-')
                                . ' - '
                                . ($detail->gramase ?? '-')
                            )
                        );

                        // =========================
                        // PRODUCTION CODE
                        // =========================
                        $sheet->setCellValue(
                            "H{$row}",
                            $detail->production_code ?? '-'
                        );

                        $sheet->setCellValue(
                            "I{$row}",
                            $inCutting
                        );

                        $sheet->setCellValue(
                            "J{$row}",
                            $packaging
                        );

                        $sheet->setCellValue(
                            "K{$row}",
                            $cl?->sampling_amount ?? '-'
                        );

                        $sheet->setCellValue(
                            "L{$row}",
                            $cl?->sampling_result ?? '-'
                        );

                        $sheet->setCellValue(
                            "M{$row}",
                            $join('sealing_condition') ?: '-'
                        );

                        $sheet->setCellValue(
                            "N{$row}",
                            $join('sealing_vacuum') ?: '-'
                        );

                        $sheet->setCellValue(
                            "O{$row}",
                            $joinContentPerPack() ?: '-'
                        );

                        $sheet->setCellValue(
                            "P{$row}",
                            $cl?->standard_long_pcs ?? '-'
                        );

                        $sheet->setCellValue(
                            "Q{$row}",
                            $join('actual_long_pcs') ?: '-'
                        );

                        $sheet->setCellValue(
                            "R{$row}",
                            $cl?->avg_long_pcs ?? '-'
                        );

                        $sheet->setCellValue(
                            "S{$row}",
                            $cl?->standard_weight_pcs ?? '-'
                        );

                        $sheet->setCellValue(
                            "T{$row}",
                            $join('actual_weight_pcs') ?: '-'
                        );

                        $sheet->setCellValue(
                            "U{$row}",
                            $cl?->avg_weight_pcs ?? '-'
                        );

                        $sheet->setCellValue(
                            "V{$row}",
                            $cl?->standard_weight ?? '-'
                        );

                        $sheet->setCellValue(
                            "W{$row}",
                            $join('actual_weight') ?: '-'
                        );

                        $sheet->setCellValue(
                            "X{$row}",
                            $cl?->avg_weight ?? '-'
                        );

                        // =========================
                        // ALIGNMENT
                        // =========================
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                            ->getAlignment()
                            ->setHorizontal('center')
                            ->setVertical('center');

                        $row++;
                        $no++;
                    }
                }

                // =========================
                // JIKA TIDAK ADA DATA
                // =========================
                if ($no === 1) {

                    $sheet->mergeCells("A5:{$lastCol}5");

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

                // =========================
                // BORDER
                // =========================
                $sheet->getStyle(
                    "A4:{$lastCol}" . ($row - 1)
                )
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');

                // =========================
                // AUTO SIZE
                // =========================
                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Tinggi header
                $sheet->getRowDimension(4)->setRowHeight(45);
            },
        ];
    }
}