<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class SmokeHouseExport implements WithEvents, WithTitle
{
    private const SHOWERING_PROCESS = 'Showering & Cooling Down';

    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Data Smoke House'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'AA';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PROSES PEMASAKAN DI SMOKE HOUSE');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                $headers = [
                    'A'  => 'No',
                    'B'  => 'Tanggal',
                    'C'  => 'Shift',
                    'D'  => 'QC',
                    'E'  => 'Nama Produk',
                    'F'  => 'Kode Produk',
                    'G'  => 'Gramasi',
                    'H'  => 'Smoke House',
                    'I'  => 'Nomor SH',
                    'J'  => 'Trolley',
                    'K'  => 'Stick',
                    'L'  => 'Waktu Proses',
                    'M'  => 'Parameter Cooking',
                    'N'  => 'Setting Suhu',
                    'O'  => 'Aktual Suhu',
                    'P'  => 'Setup Time',
                    'Q'  => 'Actual Time',
                    'R'  => 'Setting RH',
                    'S'  => 'Aktual RH',
                    'T'  => 'Setting Core',
                    'U'  => 'Aktual Core',
                    'V'  => 'Hasil Sensori',
                    'W'  => 'Notes Sensori',
                    'X'  => 'Cooking Ulang',
                    'Y'  => 'Showering & Cooling Down',
                    'Z'  => 'Cooling Finish',
                    'AA' => 'Catatan',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setWrapText(true);
                }

                $row = 5;
                $no  = 1;

                foreach ($this->reports as $report) {
                    foreach ($report->details as $detail) {

                        $cookingSteps = $detail->steps->where('process_name', '!=', self::SHOWERING_PROCESS);
                        $showeringSteps = $detail->steps->where('process_name', '==', self::SHOWERING_PROCESS);

                        $processList  = $cookingSteps->pluck('process_name')->implode(', ');
                        $settingTemp  = $cookingSteps->pluck('setting_temp')->implode(', ');
                        $actualTemp   = $cookingSteps->pluck('actual_temp')->implode(', ');
                        $settingTime  = $cookingSteps->pluck('setting_time')->implode(', ');
                        $actualTime   = $cookingSteps->pluck('actual_time')->implode(', ');
                        $settingRh    = $cookingSteps->pluck('setting_rh')->implode(', ');
                        $actualRh     = $cookingSteps->pluck('actual_rh')->implode(', ');
                        $settingCt    = $cookingSteps->pluck('setting_ct')->implode(', ');
                        $actualCt     = $cookingSteps->pluck('actual_ct')->implode(', ');

                        $waktuProses = (optional($detail->start_process)->format('H:i') ?? '-')
                            . ' - ' . (optional($detail->end_process)->format('H:i') ?? '-');

                        $sensories = $detail->sensories;
                        $sensoriText = $sensories
                            ? "Kenampakan: {$sensories->appearance}, Warna: {$sensories->color}, Aroma: {$sensories->aroma}, Rasa: {$sensories->taste}, Tekstur: {$sensories->texture}"
                            : '-';
                        $sensoriNotes = $sensories->notes ?? '-';

                        $reworkText = $detail->reworks->map(function ($rework) {
                            $waktu = (optional($rework->start_process)->format('H:i') ?? '-')
                                . '-' . (optional($rework->end_process)->format('H:i') ?? '-');

                            $steps = $rework->steps->map(function ($s) {
                                return "{$s->process_name} (Setting {$s->setting_temp}/Aktual {$s->actual_temp})";
                            })->implode('; ');

                            return "SH {$rework->smoke_house_no}, Trolley {$rework->trolley_count}, Stick {$rework->stick_count}, {$waktu}: {$steps}";
                        })->implode(' | ');

                        $showeringText = $showeringSteps->map(function ($s) {
                            return "{$s->process_name}: Setting {$s->setting_temp}/Aktual {$s->actual_temp}, "
                                . "Setup {$s->setting_time}/Actual {$s->actual_time}, "
                                . "RH {$s->setting_rh}/{$s->actual_rh}, "
                                . "Core {$s->setting_ct}/{$s->actual_ct}";
                        })->implode('; ');

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $report->shift ?? '-');
                        $sheet->setCellValue("D{$row}", $report->creator->name ?? '-');
                        $sheet->setCellValue("E{$row}", $detail->product->product_name ?? '-');
                        $sheet->setCellValue("F{$row}", $detail->production_code ?? '-');
                        $sheet->setCellValue("G{$row}", $detail->gramase ?? '-');
                        $sheet->setCellValue("H{$row}", $detail->machine_name ?? '-');
                        $sheet->setCellValue("I{$row}", $detail->smoke_house_no ?? '-');
                        $sheet->setCellValue("J{$row}", $detail->trolley_count ?? '-');
                        $sheet->setCellValue("K{$row}", $detail->stick_count ?? '-');
                        $sheet->setCellValue("L{$row}", $waktuProses);
                        $sheet->setCellValue("M{$row}", $processList ?: '-');
                        $sheet->setCellValue("N{$row}", $settingTemp ?: '-');
                        $sheet->setCellValue("O{$row}", $actualTemp ?: '-');
                        $sheet->setCellValue("P{$row}", $settingTime ?: '-');
                        $sheet->setCellValue("Q{$row}", $actualTime ?: '-');
                        $sheet->setCellValue("R{$row}", $settingRh ?: '-');
                        $sheet->setCellValue("S{$row}", $actualRh ?: '-');
                        $sheet->setCellValue("T{$row}", $settingCt ?: '-');
                        $sheet->setCellValue("U{$row}", $actualCt ?: '-');
                        $sheet->setCellValue("V{$row}", $sensoriText);
                        $sheet->setCellValue("W{$row}", $sensoriNotes);
                        $sheet->setCellValue("X{$row}", $reworkText ?: '-');
                        $sheet->setCellValue("Y{$row}", $showeringText ?: '-');
                        $sheet->setCellValue("Z{$row}", optional($detail->cooling_finish)->format('H:i') ?? '-');
                        $sheet->setCellValue("AA{$row}", $report->notes ?: '-');

                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                            ->getAlignment()->setHorizontal('center')->setWrapText(true);

                        $row++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells("A5:{$lastCol}5");
                    $sheet->setCellValue('A5', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A5')->getFont()->setItalic(true);
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                    $row++;
                }

                $sheet->getStyle("A4:{$lastCol}" . ($row - 1))->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(40);
            },
        ];
    }
}