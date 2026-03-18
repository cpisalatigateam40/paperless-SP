<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;

class FessmanCookingExport implements WithMultipleSheets
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function sheets(): array
    {
        $reports = collect($this->reports->all());
        return [
            new FessmanCookingStepsSheet($reports, $this->periodLabel),
            new FessmanCookingCoolingSheet($reports, $this->periodLabel),
        ];
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// SHEET 1 — Info + Process Steps + Sensory
// ═══════════════════════════════════════════════════════════════════════════════

class FessmanCookingStepsSheet implements WithEvents
{
    const STEPS = [
        'DRYINGI','DRYINGII','DRYINGIII','DRYINGIV','DRYINGV',
        'DOOROPENINGSECTION1','PUTCOREPROBE','SMOKING',
        'COOKINGI','COOKINGII','DRYING','STEAMSUCTION',
        'DOOROPENINGSECTION1','REMOVECOREPROBE','FURTHERTRANSPORT',
    ];

    const STEP_FIELDS = [
        ['Waktu 1',       'time_minutes_1'],
        ['Waktu 2',       'time_minutes_2'],
        ['Suhu Ruang 1',  'room_temp_1'],
        ['Suhu Ruang 2',  'room_temp_2'],
        ['Sirk. Udara 1', 'air_circulation_1'],
        ['Sirk. Udara 2', 'air_circulation_2'],
        ['Suhu Prod 1',   'product_temp_1'],
        ['Suhu Prod 2',   'product_temp_2'],
        ['Aktual Prod',   'actual_product_temp'],
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

                $infoColCount = 12;
                $stepColCount = count(self::STEPS) * count(self::STEP_FIELDS);
                $sensoryCount = 6;
                $totalCols    = $infoColCount + $stepColCount + $sensoryCount;
                $lastCol      = $this->colLetter($totalCols);

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PEMASAKAN FESSMAN — PROCESS STEPS');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                foreach (range(1, $infoColCount) as $ci) {
                    $sheet->mergeCells($this->colLetter($ci) . '4:' . $this->colLetter($ci) . '5');
                }

                $ci = $infoColCount + 1;
                foreach (self::STEPS as $stepName) {
                    $startCol = $this->colLetter($ci);
                    $endCol   = $this->colLetter($ci + count(self::STEP_FIELDS) - 1);
                    $sheet->mergeCells("{$startCol}4:{$endCol}4");
                    $sheet->setCellValue("{$startCol}4", $stepName);
                    $sheet->getStyle("{$startCol}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$startCol}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                    $ci += count(self::STEP_FIELDS);
                }

                foreach (range($ci, $totalCols) as $idx) {
                    $sheet->mergeCells($this->colLetter($idx) . '4:' . $this->colLetter($idx) . '5');
                }

                $infoSub = [
                    'No','Tanggal','Shift','Time','QC','Group','Section',
                    'No Fessman','Nama Produk','Kode Prod','Kemasan (gr)','Jml Trolley',
                ];
                foreach ($infoSub as $idx => $label) {
                    $col = $this->colLetter($idx + 1);
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $ci = $infoColCount + 1;
                foreach (self::STEPS as $_) {
                    foreach (self::STEP_FIELDS as [$label, $field]) {
                        $col = $this->colLetter($ci);
                        $sheet->setCellValue("{$col}5", $label);
                        $sheet->getStyle("{$col}5")->getFont()->setBold(true);
                        $sheet->getStyle("{$col}5")->getAlignment()
                            ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                        $ci++;
                    }
                }

                foreach (['Kematangan','Aroma','Rasa','Tekstur','Warna','Bisa Di-ulir'] as $label) {
                    $col = $this->colLetter($ci);
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                    $ci++;
                }

                $sheet->getStyle("A4:{$lastCol}5")->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                $dataRow = 6;
                $no      = 1;
                $sv = fn($v) => match((string)($v ?? '')) {
                    '1' => 'OK', '0' => 'Tidak OK', default => $v ?? '-',
                };

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        $stepsByName = $detail->processSteps->keyBy('step_name');
                        $sens        = $detail->sensoryCheck;

                        $infoVals = [
                            $no,
                            Carbon::parse($report->date)->format('d/m/Y'),
                            $shiftNum ?: ($report->shift ?? '-'),
                            Carbon::parse($report->created_at)->format('H:i'),
                            $report->created_by ?? '-',
                            $shiftGroup ?: '-',
                            $report->section->section_name ?? '-',
                            $detail->no_fessman ?? '-',
                            $detail->product->product_name ?? '-',
                            $detail->production_code ?? '-',
                            $detail->packaging_weight ?? '-',
                            $detail->trolley_count ?? '-',
                        ];
                        foreach ($infoVals as $idx => $val) {
                            $sheet->setCellValue($this->colLetter($idx + 1) . $dataRow, $val);
                        }

                        $ci = $infoColCount + 1;
                        foreach (self::STEPS as $stepName) {
                            $step = $stepsByName->get($stepName);
                            foreach (self::STEP_FIELDS as [$label, $field]) {
                                $sheet->setCellValue($this->colLetter($ci) . $dataRow, $step?->$field ?? '-');
                                $ci++;
                            }
                        }

                        foreach ([
                            $sv($sens?->ripeness), $sv($sens?->aroma),
                            $sv($sens?->taste), $sv($sens?->texture),
                            $sv($sens?->color), $sv($sens?->can_be_twisted),
                        ] as $val) {
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
// SHEET 2 — Cooling Down
// Satu baris = satu FsCoolingDown record (tidak pakai keyBy, langsung loop)
// ═══════════════════════════════════════════════════════════════════════════════

class FessmanCookingCoolingSheet implements WithEvents
{
    const COOLING_FIELDS = [
        ['Waktu 1',      'time_minutes_1'],
        ['Waktu 2',      'time_minutes_2'],
        ['RH 1',         'rh_1'],
        ['RH 2',         'rh_2'],
        ['Suhu Exit 1',  'product_temp_after_exit_1'],
        ['Suhu Exit 2',  'product_temp_after_exit_2'],
        ['Suhu Exit 3',  'product_temp_after_exit_3'],
        ['Rata-rata',    'avg_product_temp_after_exit'],
        ['Berat Mentah', 'raw_weight'],
        ['Berat Matang', 'cooked_weight'],
        ['Loss (kg)',    'loss_kg'],
        ['Loss (%)',     'loss_percent'],
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
                $sheet->setTitle('Cooling Down');
                $lastCol = 'V'; // A-I info (9) + J nama step + K-V 12 fields

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PEMASAKAN FESSMAN — COOLING DOWN');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Header row 4 — semua span row 4-5
                $headers = [
                    'A' => 'No', 'B' => 'Tanggal', 'C' => 'Shift', 'D' => 'Time',
                    'E' => 'QC', 'F' => 'Group', 'G' => 'Section',
                    'H' => 'Nama Produk', 'I' => 'Kode Prod',
                    'J' => 'Nama Tahap',
                    'K' => 'Waktu 1', 'L' => 'Waktu 2',
                    'M' => 'RH 1', 'N' => 'RH 2',
                    'O' => 'Suhu Exit 1', 'P' => 'Suhu Exit 2', 'Q' => 'Suhu Exit 3',
                    'R' => 'Rata-rata Suhu',
                    'S' => 'Berat Mentah', 'T' => 'Berat Matang',
                    'U' => 'Loss (kg)', 'V' => 'Loss (%)',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                $sheet->getStyle("A4:{$lastCol}5")->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                // Data — satu baris per cooling down record
                $dataRow = 6;
                $no      = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        // Ambil fresh dari DB jika perlu
                        $coolings = $detail->coolingDowns()->orderBy('id')->get();

                        if ($coolings->isEmpty()) {
                            // Tetap tulis baris info walau tidak ada cooling data
                            $sheet->setCellValue("A{$dataRow}", $no);
                            $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                            $sheet->setCellValue("C{$dataRow}", $shiftNum ?: ($report->shift ?? '-'));
                            $sheet->setCellValue("D{$dataRow}", Carbon::parse($report->created_at)->format('H:i'));
                            $sheet->setCellValue("E{$dataRow}", $report->created_by ?? '-');
                            $sheet->setCellValue("F{$dataRow}", $shiftGroup ?: '-');
                            $sheet->setCellValue("G{$dataRow}", $report->section->section_name ?? '-');
                            $sheet->setCellValue("H{$dataRow}", $detail->product->product_name ?? '-');
                            $sheet->setCellValue("I{$dataRow}", $detail->production_code ?? '-');
                            $sheet->setCellValue("J{$dataRow}", '-');

                            $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")
                                ->getAlignment()->setHorizontal('center');
                            $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")->getBorders()
                                ->getAllBorders()->setBorderStyle('thin');
                            $dataRow++;
                            $no++;
                            continue;
                        }

                        foreach ($coolings as $cooling) {
                            $sheet->setCellValue("A{$dataRow}", $no);
                            $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                            $sheet->setCellValue("C{$dataRow}", $shiftNum ?: ($report->shift ?? '-'));
                            $sheet->setCellValue("D{$dataRow}", Carbon::parse($report->created_at)->format('H:i'));
                            $sheet->setCellValue("E{$dataRow}", $report->created_by ?? '-');
                            $sheet->setCellValue("F{$dataRow}", $shiftGroup ?: '-');
                            $sheet->setCellValue("G{$dataRow}", $report->section->section_name ?? '-');
                            $sheet->setCellValue("H{$dataRow}", $detail->product->product_name ?? '-');
                            $sheet->setCellValue("I{$dataRow}", $detail->production_code ?? '-');
                            $sheet->setCellValue("J{$dataRow}", $cooling->step_name ?? '-');
                            $sheet->setCellValue("K{$dataRow}", $cooling->time_minutes_1 ?? '-');
                            $sheet->setCellValue("L{$dataRow}", $cooling->time_minutes_2 ?? '-');
                            $sheet->setCellValue("M{$dataRow}", $cooling->rh_1 ?? '-');
                            $sheet->setCellValue("N{$dataRow}", $cooling->rh_2 ?? '-');
                            $sheet->setCellValue("O{$dataRow}", $cooling->product_temp_after_exit_1 ?? '-');
                            $sheet->setCellValue("P{$dataRow}", $cooling->product_temp_after_exit_2 ?? '-');
                            $sheet->setCellValue("Q{$dataRow}", $cooling->product_temp_after_exit_3 ?? '-');
                            $sheet->setCellValue("R{$dataRow}", $cooling->avg_product_temp_after_exit ?? '-');
                            $sheet->setCellValue("S{$dataRow}", $cooling->raw_weight ?? '-');
                            $sheet->setCellValue("T{$dataRow}", $cooling->cooked_weight ?? '-');
                            $sheet->setCellValue("U{$dataRow}", $cooling->loss_kg ?? '-');
                            $sheet->setCellValue("V{$dataRow}", $cooling->loss_percent ?? '-');

                            $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")
                                ->getAlignment()->setHorizontal('center');
                            $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")->getBorders()
                                ->getAllBorders()->setBorderStyle('thin');

                            $dataRow++;
                        }
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells("A6:{$lastCol}6");
                    $sheet->setCellValue('A6', 'Tidak ada data.');
                    $sheet->getStyle('A6')->getFont()->setItalic(true);
                    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
                }

                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(30);
                $sheet->getRowDimension(5)->setRowHeight(30);
            },
        ];
    }
}