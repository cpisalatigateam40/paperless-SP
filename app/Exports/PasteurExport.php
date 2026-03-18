<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class PasteurExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Pasteurisasi'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Kolom layout:
                // A-H   : Info (No, Tanggal, Shift, Time, QC, Group, Produk, Kode Prod)
                // I-K   : Detail (No Program, Kemasan gr, Jml Trolley, Suhu Produk)
                // Step 1-7 (standard): masing-masing 4 kolom (Mulai, Selesai, Suhu Air, Tekanan)
                // Step 8 Drainage: 2 kolom (Mulai, Selesai)
                // Step 9 Finish: 2 kolom (Suhu Inti, Sortasi)
                // Akhir: Problem, Tindakan Koreksi

                // Step 1-7 = 7 × 4 = 28 kolom, mulai dari kolom L
                // Step 8 = 2 kolom
                // Step 9 = 2 kolom
                // Total setelah info+detail: 28+2+2 = 32 kolom + 2 kolom akhir

                // A-H (8) + I-L (4 detail) + 28 std + 2 drain + 2 finish + 2 ending = 46 kolom
                // A=1, B=2, ... Z=26, AA=27, ... AT=46

                $lastCol = 'AT';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PASTEURISASI');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Row 4: Group header ────────────────────────────────────
                // A-H info (span rows 4-5)
                foreach (['A','B','C','D','E','F','G','H'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }

                // I-L Detail (4 kolom)
                $sheet->mergeCells('I4:L4');
                $sheet->setCellValue('I4', 'Detail Produk');

                // Step 1-7: standard steps (masing-masing 4 kolom)
                $standardSteps = [
                    1 => 'Water Injection',
                    2 => 'Up Temperature',
                    3 => 'Pasteurisasi',
                    4 => 'Hot Water Recycling',
                    5 => 'Cooling Water Injection',
                    6 => 'Cooling Constant Temp.',
                    7 => 'Raw Cooling Water',
                ];

                // Kolom step 1 mulai di M (index 13)
                $colIndex = 13; // M = 13

                $stepColMap = []; // step_order => [startCol, endCol]

                foreach ($standardSteps as $order => $name) {
                    $startCol = $this->colLetter($colIndex);
                    $endCol   = $this->colLetter($colIndex + 3);
                    $sheet->mergeCells("{$startCol}4:{$endCol}4");
                    $sheet->setCellValue("{$startCol}4", "Step {$order}: {$name}");
                    $stepColMap[$order] = [$colIndex, $colIndex + 3];
                    $colIndex += 4;
                }

                // Step 8: Drainage (2 kolom)
                $drain1 = $this->colLetter($colIndex);
                $drain2 = $this->colLetter($colIndex + 1);
                $sheet->mergeCells("{$drain1}4:{$drain2}4");
                $sheet->setCellValue("{$drain1}4", 'Step 8: Drainage');
                $drainStartIdx = $colIndex;
                $colIndex += 2;

                // Step 9: Finish (2 kolom)
                $fin1 = $this->colLetter($colIndex);
                $fin2 = $this->colLetter($colIndex + 1);
                $sheet->mergeCells("{$fin1}4:{$fin2}4");
                $sheet->setCellValue("{$fin1}4", 'Step 9: Finish');
                $finStartIdx = $colIndex;
                $colIndex += 2;

                // Problem & Corrective Action (span rows 4-5)
                $prob = $this->colLetter($colIndex);
                $corr = $this->colLetter($colIndex + 1);
                $sheet->mergeCells("{$prob}4:{$prob}5");
                $sheet->setCellValue("{$prob}4", 'Problem');
                $sheet->mergeCells("{$corr}4:{$corr}5");
                $sheet->setCellValue("{$corr}4", 'Tindakan Koreksi');
                $probIdx = $colIndex;

                // Style semua group header row 4
                $sheet->getStyle("A4:{$lastCol}4")->getFont()->setBold(true);
                $sheet->getStyle("A4:{$lastCol}4")->getAlignment()
                    ->setHorizontal('center')->setVertical('center')->setWrapText(true);

                // ── Row 5: Sub-header ──────────────────────────────────────
                $infoLabels = [
                    'A' => 'No', 'B' => 'Tanggal', 'C' => 'Shift', 'D' => 'Time',
                    'E' => 'QC', 'F' => 'Group', 'G' => 'Nama Produk', 'H' => 'Kode Prod',
                ];
                foreach ($infoLabels as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                }

                // Detail sub-headers
                $detailSub = ['I' => 'No Program', 'J' => 'Kemasan (gr)', 'K' => 'Jml Trolley/pack', 'L' => 'Suhu Produk (°C)'];
                foreach ($detailSub as $col => $label) {
                    $sheet->setCellValue("{$col}5", $label);
                }

                // Standard step sub-headers
                foreach ($standardSteps as $order => $name) {
                    [$start] = $stepColMap[$order];
                    $sheet->setCellValue($this->colLetter($start)     . '5', 'Jam Mulai');
                    $sheet->setCellValue($this->colLetter($start + 1) . '5', 'Jam Selesai');
                    $sheet->setCellValue($this->colLetter($start + 2) . '5', 'Suhu Air (°C)');
                    $sheet->setCellValue($this->colLetter($start + 3) . '5', 'Tekanan (Mpa)');
                }

                // Drainage sub-headers
                $sheet->setCellValue($this->colLetter($drainStartIdx)     . '5', 'Jam Mulai');
                $sheet->setCellValue($this->colLetter($drainStartIdx + 1) . '5', 'Jam Selesai');

                // Finish sub-headers
                $sheet->setCellValue($this->colLetter($finStartIdx)     . '5', 'Suhu Inti (°C)');
                $sheet->setCellValue($this->colLetter($finStartIdx + 1) . '5', 'Sortasi');

                $sheet->getStyle("A5:{$lastCol}5")->getFont()->setBold(true);
                $sheet->getStyle("A5:{$lastCol}5")->getAlignment()
                    ->setHorizontal('center')->setVertical('center')->setWrapText(true);

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
                        // Kelompokkan steps by order
                        $stepsByOrder = $detail->steps->keyBy('step_order');

                        $sheet->setCellValue("A{$dataRow}", $no);
                        $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$dataRow}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$dataRow}", Carbon::parse($report->created_at)->format('H:i'));
                        $sheet->setCellValue("E{$dataRow}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$dataRow}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$dataRow}", $detail->product->product_name ?? '-');
                        $sheet->setCellValue("H{$dataRow}", $detail->product_code ?? '-');
                        // Detail
                        $sheet->setCellValue("I{$dataRow}", $detail->program_number ?? '-');
                        $sheet->setCellValue("J{$dataRow}", $detail->for_packaging_gr ?? '-');
                        $sheet->setCellValue("K{$dataRow}", $detail->trolley_count ?? '-');
                        $sheet->setCellValue("L{$dataRow}", $detail->product_temp ?? '-');

                        // Standard steps 1-7
                        foreach ($standardSteps as $order => $name) {
                            [$start] = $stepColMap[$order];
                            $step = $stepsByOrder->get($order);
                            $std  = $step?->standardStep;

                            $sheet->setCellValue($this->colLetter($start)     . $dataRow, $std?->start_time ?? '-');
                            $sheet->setCellValue($this->colLetter($start + 1) . $dataRow, $std?->end_time ?? '-');
                            $sheet->setCellValue($this->colLetter($start + 2) . $dataRow, $std?->water_temp ?? '-');
                            $sheet->setCellValue($this->colLetter($start + 3) . $dataRow, $std?->pressure ?? '-');
                        }

                        // Drainage step 8
                        $drain = $stepsByOrder->get(8)?->drainageStep;
                        $sheet->setCellValue($this->colLetter($drainStartIdx)     . $dataRow, $drain?->start_time ?? '-');
                        $sheet->setCellValue($this->colLetter($drainStartIdx + 1) . $dataRow, $drain?->end_time ?? '-');

                        // Finish step 9
                        $finish = $stepsByOrder->get(9)?->finishStep;
                        $sheet->setCellValue($this->colLetter($finStartIdx)     . $dataRow, $finish?->product_core_temp ?? '-');
                        $sheet->setCellValue($this->colLetter($finStartIdx + 1) . $dataRow, $finish?->sortation ?? '-');

                        // Problem & Corrective Action (dari report)
                        $sheet->setCellValue($this->colLetter($probIdx)     . $dataRow, $report->problem ?? '-');
                        $sheet->setCellValue($this->colLetter($probIdx + 1) . $dataRow, $report->corrective_action ?? '-');

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

                // Auto width semua kolom A sampai lastCol
                $totalCols = $probIdx + 2;
                for ($i = 1; $i <= $totalCols; $i++) {
                    $sheet->getColumnDimension($this->colLetter($i))->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(35);
            },
        ];
    }

    private function colLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index  = intdiv($index, 26);
        }
        return $letter;
    }
}