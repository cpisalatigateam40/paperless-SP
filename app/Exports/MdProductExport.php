<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class MdProductExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'MD Produk'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'W';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI METAL DETECTOR PRODUK');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Row 4: Group header ────────────────────────────────────
                // A-I span row 4-5 (merge vertikal)
                foreach (['A','B','C','D','E','F','G','H','I'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }
                // Fe 1.5mm : J-M
                $sheet->mergeCells('J4:M4');
                $sheet->setCellValue('J4', 'Speci. Fe 1,5 mm');
                // Non-Fe 2.0mm : N-Q
                $sheet->mergeCells('N4:Q4');
                $sheet->setCellValue('N4', 'Speci. Non-Fe 2,0 mm');
                // SUS 2.5mm : R-U
                $sheet->mergeCells('R4:U4');
                $sheet->setCellValue('R4', 'Speci. SUS 2,5 mm');
                // V-X span row 4-5
                foreach (['V','W'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }

                foreach (['J4','N4','R4'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal('center')->setVertical('center');
                }

                // ── Row 5: Sub-header ──────────────────────────────────────
                $headerLabels = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Nama Produk',
                    'H' => 'Kode Prod',
                    'I' => 'Line',
                ];

                foreach ($headerLabels as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $posLabels = ['Depan', 'Tengah', 'Belakang', 'Dalam'];
                $posCols   = [
                    'fe_1_5mm'   => ['J','K','L','M'],
                    'non_fe_2mm' => ['N','O','P','Q'],
                    'sus_2_5mm'  => ['R','S','T','U'],
                ];

                foreach ($posCols as $cols) {
                    foreach ($cols as $i => $col) {
                        $sheet->setCellValue("{$col}5", $posLabels[$i]);
                        $sheet->getStyle("{$col}5")->getFont()->setBold(true);
                        $sheet->getStyle("{$col}5")->getAlignment()
                            ->setHorizontal('center')->setVertical('center');
                    }
                }

                $sheet->setCellValue('V4', 'Tindakan Perbaikan');
                $sheet->setCellValue('W4', "Verifikasi\nSetelah Perbaikan");

                foreach (['V4','W4'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                // Border rows 4-5
                $sheet->getStyle("A4:{$lastCol}5")->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                // ── Data (mulai row 6) ─────────────────────────────────────
                $dataRow = 6;
                $no      = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        // Kelompokkan positions: [specimen][position] => status
                        $pos = [];
                        foreach ($detail->positions as $p) {
                            $pos[$p->specimen][$p->position] = $p->status ? 'OK' : 'Tidak OK';
                        }

                        $get = fn($specimen, $position) =>
                            $pos[$specimen][$position] ?? '-';

                        $verif = match ((string)($detail->verification ?? '')) {
                            '1', 'true' => 'OK',
                            '0', 'false' => 'Tidak OK',
                            default => '-',
                        };

                        $sheet->setCellValue("A{$dataRow}", $no);
                        $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$dataRow}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$dataRow}", $detail->time
                            ? Carbon::parse($detail->time)->format('H:i') : '-');
                        $sheet->setCellValue("E{$dataRow}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$dataRow}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$dataRow}", $detail->product->product_name ?? '-');
                        $sheet->setCellValue("H{$dataRow}", $detail->production_code ?? '-');
                        $sheet->setCellValue("I{$dataRow}", $detail->process_type ?? '-');
                        // Fe 1.5mm
                        $sheet->setCellValue("J{$dataRow}", $get('fe_1_5mm', 'd'));
                        $sheet->setCellValue("K{$dataRow}", $get('fe_1_5mm', 't'));
                        $sheet->setCellValue("L{$dataRow}", $get('fe_1_5mm', 'b'));
                        $sheet->setCellValue("M{$dataRow}", $get('fe_1_5mm', 'dl'));
                        // Non-Fe 2.0mm
                        $sheet->setCellValue("N{$dataRow}", $get('non_fe_2mm', 'd'));
                        $sheet->setCellValue("O{$dataRow}", $get('non_fe_2mm', 't'));
                        $sheet->setCellValue("P{$dataRow}", $get('non_fe_2mm', 'b'));
                        $sheet->setCellValue("Q{$dataRow}", $get('non_fe_2mm', 'dl'));
                        // SUS 2.5mm
                        $sheet->setCellValue("R{$dataRow}", $get('sus_2_5mm', 'd'));
                        $sheet->setCellValue("S{$dataRow}", $get('sus_2_5mm', 't'));
                        $sheet->setCellValue("T{$dataRow}", $get('sus_2_5mm', 'b'));
                        $sheet->setCellValue("U{$dataRow}", $get('sus_2_5mm', 'dl'));
                        // Koreksi & verifikasi
                        $sheet->setCellValue("V{$dataRow}", $detail->corrective_action ?? '-');
                        $sheet->setCellValue("W{$dataRow}", $verif);

                        $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")
                            ->getAlignment()->setHorizontal('center');
                        $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")->getBorders()
                            ->getAllBorders()->setBorderStyle('thin');

                        $dataRow++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells("A6:{$lastCol}6");
                    $sheet->setCellValue('A6', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A6')->getFont()->setItalic(true);
                    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
                }

                // Auto width
                $allCols = array_merge(
                    array_keys($headerLabels),
                    ['J','K','L','M','N','O','P','Q','R','S','T','U','V','W']
                );
                foreach ($allCols as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(30);
            },
        ];
    }
}