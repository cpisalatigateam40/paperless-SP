<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class RmArrivalExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Data RM Arrival';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ── Judul ──────────────────────────────────────────────────
                $sheet->mergeCells('A1:S1');
                $sheet->setCellValue('A1', 'Verifikasi Kedatangan Bahan Baku dan Bahan Penunjang (Chillroom & Seasoning)');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells('A2:S2');
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
                    'G' => 'Section',
                    'H' => 'Bahan',
                    'I' => 'Kode prod.',
                    'J' => 'Kondisi',
                    'K' => 'Supplier',
                    'L' => 'Kemasan',
                    'M' => 'Kenampakan',
                    'N' => 'Aroma',
                    'O' => 'Warna',
                    'P' => 'Kontaminasi',
                    'Q' => 'Suhu (°C)',
                    'R' => 'Problem',
                    'S' => 'Tindakan koreksi',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')
                        ->setWrapText(true);
                }

                // ── Data (mulai row 5) ─────────────────────────────────────
                $row = 5;
                $no  = 1;

                foreach ($this->reports as $report) {
                    foreach ($report->details as $detail) {

                        [$shiftNum, $shiftGroup] = array_pad(explode('-', $report->shift ?? '', 2), 2, '');

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", $report->date
                            ? Carbon::parse($report->date)->format('d/m/Y') : '-');
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$row}", $detail->time ?? '-');
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", $report->section->section_name ?? '-');
                        $sheet->setCellValue("H{$row}", $detail->rawMaterial->material_name
                            ?? $detail->premix->name
                            ?? '-');
                        $sheet->setCellValue("I{$row}", $detail->production_code ?? '-');
                        $sheet->setCellValue("J{$row}", $detail->rm_condition ?? '-');
                        $sheet->setCellValue("K{$row}", $detail->supplier ?? '-');
                        $sheet->setCellValue("L{$row}", $detail->packaging_condition ?? '-');
                        $sheet->setCellValue("M{$row}", $detail->sensory_appearance ?? '-');
                        $sheet->setCellValue("N{$row}", $detail->sensory_aroma ?? '-');
                        $sheet->setCellValue("O{$row}", $detail->sensory_color ?? '-');
                        $sheet->setCellValue("P{$row}", $detail->contamination ?? '-');
                        $sheet->setCellValue("Q{$row}", $detail->temperature ?? '-');
                        $sheet->setCellValue("R{$row}", $detail->problem ?? '-');
                        $sheet->setCellValue("S{$row}", $detail->corrective_action ?? '-');

                        $sheet->getStyle("A{$row}:S{$row}")
                            ->getAlignment()->setHorizontal('center');

                        $row++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells('A5:S5');
                    $sheet->setCellValue('A5', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A5')->getFont()->setItalic(true);
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                    $row++;
                }

                // ── Border tabel ───────────────────────────────────────────
                $sheet->getStyle("A4:S" . ($row - 1))->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                // ── Auto width ─────────────────────────────────────────────
                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(30);
            },
        ];
    }
}