<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class SiomayExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Data Siomay';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ── Judul ──────────────────────────────────────────────────
                $sheet->mergeCells('A1:W1');
                $sheet->setCellValue('A1', 'VERIFIKASI PEMBUATAN KULIT SIOMAY, GIOZA & MANDU');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells('A2:W2');
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
                    'G' => 'Nama Produk',
                    'H' => 'Kode Prod',
                    'I' => 'Waktu Start',
                    'J' => 'Waktu Stop',
                    'K' => 'Bahan',
                    'L' => 'Berat',
                    'M' => 'Sensori RM',
                    'N' => 'Lama Proses',
                    'O' => 'Mixing Paddle',
                    'P' => 'Pressure',
                    'Q' => 'Target Temp. (°C)',
                    'R' => 'Aktual Temp. (°C)',
                    'S' => 'Sensori Warna',
                    'T' => 'Sensori Aroma',
                    'U' => 'Sensori Rasa',
                    'V' => 'Sensori Tekstur',
                    'W' => 'Catatan',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setWrapText(true);
                }

                // ── Data (mulai row 5) ─────────────────────────────────────
                // Satu baris = satu detail proses.
                // Bahan (K), Berat (L), Sensori RM (M) digabung koma jika ada
                // beberapa raw material dalam satu detail.
                $row = 5;
                $no  = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        // Gabungkan raw materials jadi satu baris
                        $bahanList   = $detail->rawMaterials
                            ->map(fn($rm) => $rm->rawMaterial->material_name ?? '-')
                            ->implode(', ');

                        $beratList   = $detail->rawMaterials
                            ->map(fn($rm) => $rm->amount ?? '-')
                            ->implode(', ');

                        $sensoriRmList = $detail->rawMaterials
                            ->map(fn($rm) => $rm->sensory ?? '-')
                            ->implode(', ');

                        // Mixing paddle
                        $mixingPaddle = '-';
                        if ($detail->mixing_paddle_on)  $mixingPaddle = 'On';
                        if ($detail->mixing_paddle_off) $mixingPaddle = 'Off';

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $time = ($report->start_time && $report->end_time)
                            ? $report->start_time . ' - ' . $report->end_time
                            : ($report->start_time ?? '-');
                        $sheet->setCellValue("D{$row}", $time);
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", $report->product->product_name ?? '-');
                        $sheet->setCellValue("H{$row}", $report->production_code ?? '-');
                        $sheet->setCellValue("I{$row}", $report->start_time ?? '-');
                        $sheet->setCellValue("J{$row}", $report->end_time ?? '-');
                        $sheet->setCellValue("K{$row}", $bahanList ?: '-');
                        $sheet->setCellValue("L{$row}", $beratList ?: '-');
                        $sheet->setCellValue("M{$row}", $sensoriRmList ?: '-');
                        $sheet->setCellValue("N{$row}", $detail->duration ?? '-');
                        $sheet->setCellValue("O{$row}", $mixingPaddle);
                        $sheet->setCellValue("P{$row}", $detail->pressure ?? '-');
                        $sheet->setCellValue("Q{$row}", $detail->target_temperature ?? '-');
                        $sheet->setCellValue("R{$row}", $detail->actual_temperature ?? '-');
                        $sheet->setCellValue("S{$row}", $detail->color ?? '-');
                        $sheet->setCellValue("T{$row}", $detail->aroma ?? '-');
                        $sheet->setCellValue("U{$row}", $detail->taste ?? '-');
                        $sheet->setCellValue("V{$row}", $detail->texture ?? '-');
                        $sheet->setCellValue("W{$row}", $detail->notes ?? '-');

                        $sheet->getStyle("A{$row}:W{$row}")
                            ->getAlignment()->setHorizontal('center');

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