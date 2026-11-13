<?php

namespace App\Exports;

use App\Models\MasterPasteurStep;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class PasteurRunSheet implements WithEvents, WithTitle
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
                $MPS   = MasterPasteurStep::class;
                $steps = $MPS::STEPS;

                $info = [
                    'No', 'Tanggal', 'Shift', 'Time', 'QC', 'Group',
                    'Nama Produk', 'Kode Prod', 'Gramasi (gr)', 'No Program',
                    'Putaran', 'No Retort', 'Jml Trolly', 'Jml Tray',
                    'Jam Mulai', 'Jam Selesai', 'Total Time (menit)',
                    'Initial Temp Std (°C)', 'Initial Temp Act (°C)',
                ];

                $stepSub   = ['Set Time (menit)', 'Act Time (menit)', 'Set Water Temp (°C)',
                              'Act Water Temp (°C)', 'Set Pressure (Mpa)', 'Act Pressure (Mpa)'];
                $stepStart = count($info) + 1;                       // kolom pertama step 1
                $tailStart = $stepStart + count($steps) * count($stepSub);
                $lastIdx   = $tailStart + 3;                         // Suhu Pusat, Sensori, Notes, Catatan
                $lastCol   = $this->colLetter($lastIdx);

                // ── Judul ─────────────────────────────────────────────
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Verifikasi Proses Pasteurisasi Produk di Retort Chamber');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Header baris 4-5 ──────────────────────────────────
                foreach ($info as $i => $label) {
                    $c = $this->colLetter($i + 1);
                    $sheet->mergeCells("{$c}4:{$c}5");
                    $sheet->setCellValue("{$c}4", $label);
                }

                $idx = $stepStart;
                foreach ($steps as $order => $name) {
                    $a = $this->colLetter($idx);
                    $b = $this->colLetter($idx + count($stepSub) - 1);
                    $sheet->mergeCells("{$a}4:{$b}4");
                    $sheet->setCellValue("{$a}4", "Step {$order}: {$name}");

                    foreach ($stepSub as $k => $label) {
                        $sheet->setCellValue($this->colLetter($idx + $k) . '5', $label);
                    }
                    $idx += count($stepSub);
                }

                foreach (['Suhu Pusat Produk (°C)', 'Sensori', 'Notes', 'Catatan'] as $k => $label) {
                    $c = $this->colLetter($tailStart + $k);
                    $sheet->mergeCells("{$c}4:{$c}5");
                    $sheet->setCellValue("{$c}4", $label);
                }

                $sheet->getStyle("A4:{$lastCol}5")->getFont()->setBold(true);
                $sheet->getStyle("A4:{$lastCol}5")->getAlignment()
                    ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                $sheet->getStyle("A4:{$lastCol}5")->getFill()->setFillType('solid')
                    ->getStartColor()->setRGB('E9ECF5');
                $sheet->getStyle("A4:{$lastCol}5")->getBorders()->getAllBorders()->setBorderStyle('thin');

                // ── Data (mulai baris 6) ──────────────────────────────
                $row = 6;
                $no  = 1;

                $num  = fn ($v) => (is_null($v) || $v === '') ? '-' : (float) $v;
                $time = fn ($t) => $t ? substr($t, 0, 5) : '-';

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(explode('-', $report->shift ?? '', 2), 2, '');

                    foreach ($report->details as $detail) {
                        if ($detail->isLegacy()) continue;   // ada di sheet format lama

                        foreach ($detail->runs as $run) {
                            $byOrder = $run->steps->keyBy('step_order');
                            $finish  = $byOrder->get(count($steps) + 1)?->finishStep;
                            $gram    = !empty($detail->for_packaging_gr)
                                ? $detail->for_packaging_gr
                                : ($detail->product->nett_weight ?? null);

                            $put = fn (int $i, $v) => $sheet->setCellValue($this->colLetter($i) . $row, $v);

                            $put(1,  $no);
                            $put(2,  Carbon::parse($report->date)->format('d/m/Y'));
                            $put(3,  $shiftNum ?: ($report->shift ?? '-'));
                            $put(4,  Carbon::parse($report->created_at)->format('H:i'));
                            $put(5,  $report->created_by ?? '-');
                            $put(6,  $shiftGroup ?: '-');
                            $put(7,  $detail->product->product_name ?? '-');
                            $put(8,  $detail->product_code ?? '-');
                            $put(9,  $num($gram));
                            $put(10, $detail->program_number ?? '-');
                            $put(11, $run->attempt == 1
                                ? 'Utama'
                                : 'Ulang' . ($run->attempt > 2 ? ' #' . ($run->attempt - 1) : ''));
                            $put(12, $run->retort_chamber_number ?? '-');
                            $put(13, $num($run->trolley_count));
                            $put(14, $num($run->tray_count));
                            $put(15, $time($run->start_time));
                            $put(16, $time($run->end_time));
                            $put(17, $run->total_minutes ?? '-');
                            $put(18, $MPS::rangeLabel($run->initial_temp_min, $run->initial_temp_max));
                            $put(19, $num($run->initial_temp));

                            if ($MPS::outOfStd($run->initial_temp, $run->initial_temp_min, $run->initial_temp_max)) {
                                $this->red($sheet, 19, $row);
                            }

                            $idx = $stepStart;
                            foreach ($steps as $order => $name) {
                                $s = $byOrder->get($order)?->standardStep;

                                $put($idx,     $num($s?->set_time));
                                $put($idx + 1, $num($s?->act_time));
                                $put($idx + 2, $MPS::rangeLabel($s?->set_water_temp_min, $s?->set_water_temp_max));
                                $put($idx + 3, $num($s?->water_temp));
                                $put($idx + 4, $MPS::rangeLabel($s?->set_pressure_min, $s?->set_pressure_max));
                                $put($idx + 5, $num($s?->pressure));

                                if ($MPS::outOfStd($s?->act_time, $s?->set_time, null, 0)) {
                                    $this->red($sheet, $idx + 1, $row);
                                }
                                if ($MPS::outOfStd($s?->water_temp, $s?->set_water_temp_min, $s?->set_water_temp_max)) {
                                    $this->red($sheet, $idx + 3, $row);
                                }
                                if ($MPS::outOfStd($s?->pressure, $s?->set_pressure_min, $s?->set_pressure_max)) {
                                    $this->red($sheet, $idx + 5, $row);
                                }

                                $idx += count($stepSub);
                            }

                            $put($tailStart,     $num($finish?->product_core_temp));
                            $put($tailStart + 1, is_null($run->sensor_ok) ? '-' : ($run->sensor_ok ? 'Ok' : 'Tidak Ok'));
                            $put($tailStart + 2, $run->notes ?: '-');
                            $put($tailStart + 3, $report->problem ?: '-');

                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()
                                ->setHorizontal('center')->setVertical('center');
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getBorders()
                                ->getAllBorders()->setBorderStyle('thin');

                            // kolom Set diberi latar abu tipis (mengikuti tampilan form)
                            $idx = $stepStart;
                            foreach ($steps as $order => $name) {
                                foreach ([0, 2, 4] as $off) {
                                    $c = $this->colLetter($idx + $off) . $row;
                                    $sheet->getStyle($c)->getFill()->setFillType('solid')
                                        ->getStartColor()->setRGB('F5F6FA');
                                }
                                $idx += count($stepSub);
                            }

                            $row++;
                            $no++;
                        }
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells("A6:{$lastCol}6");
                    $sheet->setCellValue('A6', 'Tidak ada data format baru untuk periode yang dipilih.');
                    $sheet->getStyle('A6')->getFont()->setItalic(true);
                    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
                }

                // ── Tampilan ──────────────────────────────────────────
                for ($i = 1; $i <= $lastIdx; $i++) {
                    $sheet->getColumnDimension($this->colLetter($i))->setAutoSize(true);
                }
                $sheet->getRowDimension(4)->setRowHeight(22);
                $sheet->getRowDimension(5)->setRowHeight(35);
                $sheet->freezePane('H6');   // kolom A-G (info) dan header tetap terlihat saat scroll
            },
        ];
    }

    private function red($sheet, int $col, int $row): void
    {
        $font = $sheet->getStyle($this->colLetter($col) . $row)->getFont();
        $font->setBold(true)->getColor()->setRGB('CC0000');
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