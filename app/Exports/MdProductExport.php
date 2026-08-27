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
                $lastCol = 'X';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Verifikasi Kinerja Metal Detector Produk');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Row 4: Group header ────────────────────────────────────
                // A-L span row 4-5 (merge vertikal)
                foreach (['A','B','C','D','E','F','G','H','I','J','K','L'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }
                // Fe 1.5mm : M-O
                $sheet->mergeCells('M4:O4');
                $sheet->setCellValue('M4', 'Speci. Fe 1,5 mm');
                // Non-Fe 2.0mm : P-R
                $sheet->mergeCells('P4:R4');
                $sheet->setCellValue('P4', 'Speci. Non-Fe 2,0 mm');
                // SUS 2.5mm : S-U
                $sheet->mergeCells('S4:U4');
                $sheet->setCellValue('S4', 'Speci. SUS 2,5 mm');
                // V-X span row 4-5
                foreach (['V','W','X'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }

                foreach (['M4','P4','S4'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal('center')->setVertical('center');
                }

                // ── Row 5: Sub-header ──────────────────────────────────────
                $headerLabels = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Waktu Verifikasi',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Merk',
                    'H' => 'Type/Model',
                    'I' => 'No. Series',
                    'J' => 'Nama Produk',
                    'K' => 'Gramase (gr)',
                    'L' => 'Kode Produksi',
                ];

                foreach ($headerLabels as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $posLabels = ['Depan', 'Tengah', 'Belakang'];
                $posCols   = [
                    'fe_1_5mm'   => ['M','N','O'],
                    'non_fe_2mm' => ['P','Q','R'],
                    'sus_2_5mm'  => ['S','T','U'],
                ];

                foreach ($posCols as $cols) {
                    foreach ($cols as $i => $col) {
                        $sheet->setCellValue("{$col}5", $posLabels[$i]);
                        $sheet->getStyle("{$col}5")->getFont()->setBold(true);
                        $sheet->getStyle("{$col}5")->getAlignment()
                            ->setHorizontal('center')->setVertical('center');
                    }
                }

                $sheet->setCellValue('V4', 'Status (OK/NG)');
                $sheet->setCellValue('W4', 'Tindakan Koreksi');
                $sheet->setCellValue('X4', 'Keterangan');

                foreach (['V4','W4','X4'] as $cell) {
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

                    $merk      = $report->metalDetector->merk ?? '-';
                    $typeModel = $report->metalDetector->type_model ?? '-';
                    $noSeries  = $report->metalDetector->no_series ?? '-';

                    foreach ($report->details as $detail) {
                        // Kelompokkan positions: [specimen][position] => status
                        $pos = [];
                        foreach ($detail->positions as $p) {
                            $pos[$p->specimen][$p->position] = $p->status ? 'OK' : 'Tidak OK';
                        }

                        $get = fn($specimen, $position) =>
                            $pos[$specimen][$position] ?? '-';

                        $status = $detail->status ? 'OK' : 'NG';

                        $sheet->setCellValue("A{$dataRow}", $no);
                        $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$dataRow}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$dataRow}", $detail->time
                            ? Carbon::parse($detail->time)->format('H:i') : '-');
                        $sheet->setCellValue("E{$dataRow}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$dataRow}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$dataRow}", $merk);
                        $sheet->setCellValue("H{$dataRow}", $typeModel);
                        $sheet->setCellValue("I{$dataRow}", $noSeries);
                        $sheet->setCellValue("J{$dataRow}", $detail->product->product_name ?? '-');
                        $sheet->setCellValue("K{$dataRow}", $detail->gramase ?? '-');
                        $sheet->setCellValue("L{$dataRow}", $detail->production_code ?? '-');
                        // Fe 1.5mm
                        $sheet->setCellValue("M{$dataRow}", $get('fe_1_5mm', 'd'));
                        $sheet->setCellValue("N{$dataRow}", $get('fe_1_5mm', 't'));
                        $sheet->setCellValue("O{$dataRow}", $get('fe_1_5mm', 'b'));
                        // Non-Fe 2.0mm
                        $sheet->setCellValue("P{$dataRow}", $get('non_fe_2mm', 'd'));
                        $sheet->setCellValue("Q{$dataRow}", $get('non_fe_2mm', 't'));
                        $sheet->setCellValue("R{$dataRow}", $get('non_fe_2mm', 'b'));
                        // SUS 2.5mm
                        $sheet->setCellValue("S{$dataRow}", $get('sus_2_5mm', 'd'));
                        $sheet->setCellValue("T{$dataRow}", $get('sus_2_5mm', 't'));
                        $sheet->setCellValue("U{$dataRow}", $get('sus_2_5mm', 'b'));
                        // Status, koreksi & keterangan
                        $sheet->setCellValue("V{$dataRow}", $status);
                        $sheet->setCellValue("W{$dataRow}", $detail->corrective_action ?? '-');
                        $sheet->setCellValue("X{$dataRow}", $detail->verification ?? '-');

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
                    ['M','N','O','P','Q','R','S','T','U','V','W','X']
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