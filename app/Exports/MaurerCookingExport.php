<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;

class MaurerCookingExport implements WithMultipleSheets
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function sheets(): array
    {
        $reports = collect($this->reports->all());
        return [
            new MaurerCookingStepsSheet($reports, $this->periodLabel),
            new MaurerCookingShoweringSheet($reports, $this->periodLabel),
        ];
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// SHEET 1 — Info + Process Steps
// ═══════════════════════════════════════════════════════════════════════════════

class MaurerCookingStepsSheet implements WithEvents
{
    // 11 step names sesuai form
    const STEPS = [
        'SHOWERING','WARMING','DRYINGI','DRYINGII','DRYINGIII',
        'DRYINGIV','DRYINGV','SMOKING','COOKINGI','COOKINGII','EVAKUASI',
    ];

    // Field per step: [label singkat, field_name]
    const STEP_FIELDS = [
        ['ST Ruang',     'room_temperature_1'],
        ['AT Ruang',     'room_temperature_2'],
        ['ST RH',        'rh_1'],
        ['AT RH',        'rh_2'],
        ['ST Waktu',     'time_minutes_1'],
        ['AT Waktu',     'time_minutes_2'],
        ['ST Suhu Prod', 'product_temperature_1'],
        ['AT Suhu Prod', 'product_temperature_2'],
    ];

    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setTitle('Process Steps');

                // Kolom:
                // A-J    : Info (No, Tgl, Shift, Time, QC, Group, Section, Produk, Kode Prod, Kemasan gr)
                // K-L    : Trolley, Bisa Di-ulir
                // M-N    : Total Proses (Start, End, Durasi)
                // O+     : Per step × 8 field = 11×8 = 88 kolom → O ~ DH
                // Thermocouple & Sensory di akhir

                // Hitung last col dinamis
                $infoColCount   = 14; // A-N (info + total process)
                $stepColCount   = count(self::STEPS) * count(self::STEP_FIELDS); // 11×8=88
                $extraColCount  = 2; // thermocouple + can_be_twisted
                $sensoryCount   = 5; // ripeness,aroma,texture,color,taste
                $totalCols      = $infoColCount + $stepColCount + $extraColCount + $sensoryCount;
                $lastCol        = $this->colLetter($totalCols);

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PEMASAKAN MAURER — PROCESS STEPS');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Row 4: Group header ────────────────────────────────────
                // A-N span rows 4-5
                foreach (range(1, $infoColCount) as $ci) {
                    $col = $this->colLetter($ci);
                    $sheet->mergeCells("{$col}4:{$col}5");
                }

                // Step group headers
                $ci = $infoColCount + 1;
                foreach (self::STEPS as $stepName) {
                    $startCol = $this->colLetter($ci);
                    $endCol   = $this->colLetter($ci + count(self::STEP_FIELDS) - 1);
                    $sheet->mergeCells("{$startCol}4:{$endCol}4");
                    $sheet->setCellValue("{$startCol}4", $stepName);
                    $sheet->getStyle("{$startCol}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$startCol}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center');
                    $ci += count(self::STEP_FIELDS);
                }

                // Extra & sensory span rows 4-5
                foreach (range($ci, $totalCols) as $idx) {
                    $col = $this->colLetter($idx);
                    $sheet->mergeCells("{$col}4:{$col}5");
                }

                // ── Row 5: Sub-header ──────────────────────────────────────
                $infoSub = [
                    'No','Tanggal','Shift','Time','QC','Group','Section',
                    'Nama Produk','Kode Prod','Kemasan (gr)','Jml Trolley',
                    'Bisa Di-ulir','Mulai Proses','Selesai Proses',
                ];
                foreach ($infoSub as $idx => $label) {
                    $col = $this->colLetter($idx + 1);
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                // Step sub-headers
                $ci = $infoColCount + 1;
                foreach (self::STEPS as $stepName) {
                    foreach (self::STEP_FIELDS as [$label, $field]) {
                        $col = $this->colLetter($ci);
                        $sheet->setCellValue("{$col}5", $label);
                        $sheet->getStyle("{$col}5")->getFont()->setBold(true);
                        $sheet->getStyle("{$col}5")->getAlignment()
                            ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                        $ci++;
                    }
                }

                // Sensory sub-headers
                $sensorySub = ['Thermocouple','Kematangan','Aroma','Tekstur','Warna','Rasa','Durasi (mnt)'];
                foreach ($sensorySub as $label) {
                    $col = $this->colLetter($ci);
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                    $ci++;
                }

                $sheet->getStyle("A4:{$lastCol}5")->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                // ── Data ──────────────────────────────────────────────────
                $dataRow = 6;
                $no      = 1;
                $sensoriVal = fn($v) => match((string)($v ?? '')) {
                    '1' => 'OK', '0' => 'Tidak OK', default => $v ?? '-',
                };

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        $stepsByName = $detail->processSteps->keyBy('step_name');
                        $tc   = $detail->thermocouplePositions->first()?->position_info ?? '-';
                        $sens = $detail->sensoryCheck;
                        $tpt  = $detail->totalProcessTime;

                        // Info
                        $infoVals = [
                            $no,
                            Carbon::parse($report->date)->format('d/m/Y'),
                            $shiftNum ?: ($report->shift ?? '-'),
                            Carbon::parse($report->created_at)->format('H:i'),
                            $report->created_by ?? '-',
                            $shiftGroup ?: '-',
                            $report->section->section_name ?? '-',
                            $detail->product->product_name ?? '-',
                            $detail->production_code ?? '-',
                            $detail->packaging_weight ?? '-',
                            $detail->trolley_count ?? '-',
                            $detail->can_be_twisted ? 'Bisa' : 'Tidak Bisa',
                            $tpt?->start_time ?? '-',
                            $tpt?->end_time ?? '-',
                        ];
                        foreach ($infoVals as $idx => $val) {
                            $sheet->setCellValue($this->colLetter($idx + 1) . $dataRow, $val);
                        }

                        // Steps
                        $ci = $infoColCount + 1;
                        foreach (self::STEPS as $stepName) {
                            $step = $stepsByName->get($stepName);
                            foreach (self::STEP_FIELDS as [$label, $field]) {
                                $sheet->setCellValue($this->colLetter($ci) . $dataRow, $step?->$field ?? '-');
                                $ci++;
                            }
                        }

                        // Thermocouple + Sensory + Durasi
                        $extraVals = [
                            $tc,
                            $sensoriVal($sens?->ripeness),
                            $sensoriVal($sens?->aroma),
                            $sensoriVal($sens?->texture),
                            $sensoriVal($sens?->color),
                            $sensoriVal($sens?->taste),
                            $tpt?->total_duration ?? '-',
                        ];
                        foreach ($extraVals as $val) {
                            $sheet->setCellValue($this->colLetter($ci) . $dataRow, $val);
                            $ci++;
                        }

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
                    $sheet->setCellValue('A6', 'Tidak ada data.');
                    $sheet->getStyle('A6')->getFont()->setItalic(true);
                    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
                }

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

// ═══════════════════════════════════════════════════════════════════════════════
// SHEET 2 — Showering & Cooling Down
// ═══════════════════════════════════════════════════════════════════════════════

class MaurerCookingShoweringSheet implements WithEvents
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setTitle('Showering & Cooling Down');
                $lastCol = 'U';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PEMASAKAN MAURER — SHOWERING & COOLING DOWN');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Row 4: group headers
                foreach (['A','B','C','D','E','F','G','H','I'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }
                $sheet->mergeCells('J4:J5');
                $sheet->mergeCells('K4:P4'); $sheet->setCellValue('K4', 'Cooling Down');
                $sheet->mergeCells('Q4:S4'); $sheet->setCellValue('Q4', "Suhu Pusat Produk\nSetelah Keluar (°C)");
                $sheet->mergeCells('T4:T5'); $sheet->setCellValue('T4', "Rata-rata Suhu\nSetelah Keluar (°C)");
                $sheet->mergeCells('U4:U5'); $sheet->setCellValue('U4', 'Nama Produk');

                foreach (['K4','Q4'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $infoSub = [
                    'A' => 'No', 'B' => 'Tanggal', 'C' => 'Shift', 'D' => 'Time',
                    'E' => 'QC', 'F' => 'Group', 'G' => 'Section',
                    'H' => 'Kode Prod', 'I' => 'Jml Trolley',
                    'J' => 'Showering Time',
                ];
                foreach ($infoSub as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $sub5 = [
                    'K' => "Suhu Ruang\nStd (°C)",
                    'L' => "Suhu Ruang\nAktual (°C)",
                    'M' => "Suhu Produk\nStd (°C)",
                    'N' => "Suhu Produk\nAktual (°C)",
                    'O' => "Waktu\nStd (mnt)",
                    'P' => "Waktu\nAktual (mnt)",
                    'Q' => '1', 'R' => '2', 'S' => '3',
                ];
                foreach ($sub5 as $col => $label) {
                    $sheet->setCellValue("{$col}5", $label);
                    $sheet->getStyle("{$col}5")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}5")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $sheet->getStyle("A4:{$lastCol}5")->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                // Data
                $dataRow = 6;
                $no      = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        $scd = $detail->showeringCoolingDown;

                        $sheet->setCellValue("A{$dataRow}", $no);
                        $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$dataRow}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$dataRow}", Carbon::parse($report->created_at)->format('H:i'));
                        $sheet->setCellValue("E{$dataRow}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$dataRow}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$dataRow}", $report->section->section_name ?? '-');
                        $sheet->setCellValue("H{$dataRow}", $detail->production_code ?? '-');
                        $sheet->setCellValue("I{$dataRow}", $detail->trolley_count ?? '-');
                        $sheet->setCellValue("J{$dataRow}", $scd?->showering_time ?? '-');
                        // Cooling Down
                        $sheet->setCellValue("K{$dataRow}", $scd?->room_temp_1 ?? '-');
                        $sheet->setCellValue("L{$dataRow}", $scd?->room_temp_2 ?? '-');
                        $sheet->setCellValue("M{$dataRow}", $scd?->product_temp_1 ?? '-');
                        $sheet->setCellValue("N{$dataRow}", $scd?->product_temp_2 ?? '-');
                        $sheet->setCellValue("O{$dataRow}", $scd?->time_minutes_1 ?? '-');
                        $sheet->setCellValue("P{$dataRow}", $scd?->time_minutes_2 ?? '-');
                        // Suhu pusat produk setelah keluar
                        $sheet->setCellValue("Q{$dataRow}", $scd?->product_temp_after_exit_1 ?? '-');
                        $sheet->setCellValue("R{$dataRow}", $scd?->product_temp_after_exit_2 ?? '-');
                        $sheet->setCellValue("S{$dataRow}", $scd?->product_temp_after_exit_3 ?? '-');
                        $sheet->setCellValue("T{$dataRow}", $scd?->avg_product_temp_after_exit ?? '-');
                        $sheet->setCellValue("U{$dataRow}", $detail->product->product_name ?? '-');

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
                    $sheet->setCellValue('A6', 'Tidak ada data.');
                    $sheet->getStyle('A6')->getFont()->setItalic(true);
                    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
                }

                foreach (range('A', 'U') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(35);
            },
        ];
    }
}