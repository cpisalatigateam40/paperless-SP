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
                $lastCol = 'T';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Verifikasi Kinerja Metal Detector Produk');
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
                // Fe 1.5mm : J-L
                $sheet->mergeCells('J4:L4');
                $sheet->setCellValue('J4', 'Speci. Fe 1,5 mm');
                // Non-Fe 2.0mm : M-O
                $sheet->mergeCells('M4:O4');
                $sheet->setCellValue('M4', 'Speci. Non-Fe 2,0 mm');
                // SUS 2.5mm : P-R
                $sheet->mergeCells('P4:R4');
                $sheet->setCellValue('P4', 'Speci. SUS 2,5 mm');
                // S-T span row 4-5
                foreach (['S','T'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }

                foreach (['J4','M4','P4'] as $cell) {
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

                $posLabels = ['Depan', 'Tengah', 'Belakang'];
                $posCols   = [
                    'fe_1_5mm'   => ['J','K','L'],
                    'non_fe_2mm' => ['M','N','O'],
                    'sus_2_5mm'  => ['P','Q','R'],
                ];

                foreach ($posCols as $cols) {
                    foreach ($cols as $i => $col) {
                        $sheet->setCellValue("{$col}5", $posLabels[$i]);
                        $sheet->getStyle("{$col}5")->getFont()->setBold(true);
                        $sheet->getStyle("{$col}5")->getAlignment()
                            ->setHorizontal('center')->setVertical('center');
                    }
                }

                $sheet->setCellValue('S4', 'Tindakan Perbaikan');
                $sheet->setCellValue('T4', "Verifikasi\nSetelah Perbaikan");

                foreach (['S4','T4'] as $cell) {
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
                        $sheet->setCellValue(
                            "G{$dataRow}",
                            trim(($detail->product->product_name ?? '-') . ' - ' . ($detail->gramase ?? '-'))
                        );
                        $sheet->setCellValue("H{$dataRow}", $detail->production_code ?? '-');
                        $sheet->setCellValue("I{$dataRow}", $detail->process_type ?? '-');
                        // Fe 1.5mm
                        $sheet->setCellValue("J{$dataRow}", $get('fe_1_5mm', 'd'));
                        $sheet->setCellValue("K{$dataRow}", $get('fe_1_5mm', 't'));
                        $sheet->setCellValue("L{$dataRow}", $get('fe_1_5mm', 'b'));
                        // Non-Fe 2.0mm
                        $sheet->setCellValue("M{$dataRow}", $get('non_fe_2mm', 'd'));
                        $sheet->setCellValue("N{$dataRow}", $get('non_fe_2mm', 't'));
                        $sheet->setCellValue("O{$dataRow}", $get('non_fe_2mm', 'b'));
                        // SUS 2.5mm
                        $sheet->setCellValue("P{$dataRow}", $get('sus_2_5mm', 'd'));
                        $sheet->setCellValue("Q{$dataRow}", $get('sus_2_5mm', 't'));
                        $sheet->setCellValue("R{$dataRow}", $get('sus_2_5mm', 'b'));
                        // Koreksi & verifikasi
                        $sheet->setCellValue("S{$dataRow}", $detail->corrective_action ?? '-');
                        $sheet->setCellValue("T{$dataRow}", $verif);

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
                    ['J','K','L','M','N','O','P','Q','R','S','T']
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