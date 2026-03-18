<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class GmpSanitationExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Sanitasi'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'R';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN KONTROL SANITASI');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Kolom: no, tanggal, shift, time (hour_1 - hour_2), qc, group,
                //        std klorin foot basin, aktual klorin foot basin, suhu foot basin,
                //        std klorin hand basin, aktual klorin hand basin, suhu hand basin,
                //        suhu air cuci tangan, suhu air cleaning,
                //        keterangan, tindakan koreksi, verifikasi
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Area',
                    'H' => "Std. Klorin\nFoot Basin",
                    'I' => "Aktual Klorin\nFoot Basin",
                    'J' => "Suhu\nFoot Basin (°C)",
                    'K' => "Std. Klorin\nHand Basin",
                    'L' => "Aktual Klorin\nHand Basin",
                    'M' => "Suhu\nHand Basin (°C)",
                    'N' => "Suhu Air\nCuci Tangan (°C)",
                    'O' => "Suhu Air\nCleaning (°C)",
                    'P' => 'Keterangan',
                    'Q' => 'Tindakan Koreksi',
                    'R' => 'Verifikasi',
                    
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
                    $check = $report->sanitationCheck;
                    if (!$check) continue;

                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    // Time dari hour_1 - hour_2
                    $time = ($check->hour_1 && $check->hour_2)
                        ? $check->hour_1 . ' - ' . $check->hour_2
                        : ($check->hour_1 ?? '-');

                    // Kelompokkan sanitation areas berdasarkan area_name
                    $areasByName = $check->sanitationArea->keyBy('area_name');

                    $footBasin      = $areasByName->get('Foot Basin');
                    $handBasin      = $areasByName->get('Hand Basin');
                    $airCuciTangan  = $areasByName->get('Air Cuci Tangan');
                    $airCleaning    = $areasByName->get('Air Cleaning');

                    // Helper ambil hasil per hour_to (1=awal shift, 2=setelah istirahat)
                    $getResult = fn($area, $hourTo, $field) =>
                        $area?->sanitationResult
                            ->firstWhere('hour_to', $hourTo)
                            ?->$field ?? '-';

                    // Verifikasi: gabungkan semua area yang tidak OK
                    $allVerif = collect([$footBasin, $handBasin, $airCuciTangan, $airCleaning])
                        ->filter()
                        ->map(fn($a) => match ((string) ($a->verification ?? '')) {
                            '1' => 'OK', '0' => 'Tidak OK', default => '-'
                        })
                        ->unique()
                        ->implode(', ');

                    // Keterangan & koreksi: gabungkan dari semua area
                    $keterangan = collect([$footBasin, $handBasin, $airCuciTangan, $airCleaning])
                        ->filter(fn($a) => $a && $a->notes)
                        ->map(fn($a) => $a->area_name . ': ' . $a->notes)
                        ->implode('; ');

                    $koreksi = collect([$footBasin, $handBasin, $airCuciTangan, $airCleaning])
                        ->filter(fn($a) => $a && $a->corrective_action)
                        ->map(fn($a) => $a->area_name . ': ' . $a->corrective_action)
                        ->implode('; ');

                    // Ambil aktual klorin & suhu dari result hour_to=1 (awal shift)
                    // jika hour_to=2 ada, ambil juga (setelah istirahat) — gunakan yang terbaru
                    $footActualKlorin = $getResult($footBasin, 1, 'chlorine_level') !== '-'
                        ? $getResult($footBasin, 1, 'chlorine_level')
                        : $getResult($footBasin, 2, 'chlorine_level');
                    $footSuhu = $getResult($footBasin, 1, 'temperature') !== '-'
                        ? $getResult($footBasin, 1, 'temperature')
                        : $getResult($footBasin, 2, 'temperature');

                    $handActualKlorin = $getResult($handBasin, 1, 'chlorine_level') !== '-'
                        ? $getResult($handBasin, 1, 'chlorine_level')
                        : $getResult($handBasin, 2, 'chlorine_level');
                    $handSuhu = $getResult($handBasin, 1, 'temperature') !== '-'
                        ? $getResult($handBasin, 1, 'temperature')
                        : $getResult($handBasin, 2, 'temperature');

                    $suhuCuciTangan = $getResult($airCuciTangan, 1, 'temperature') !== '-'
                        ? $getResult($airCuciTangan, 1, 'temperature')
                        : $getResult($airCuciTangan, 2, 'temperature');

                    $suhuCleaning = $getResult($airCleaning, 1, 'temperature') !== '-'
                        ? $getResult($airCleaning, 1, 'temperature')
                        : $getResult($airCleaning, 2, 'temperature');

                    $sheet->setCellValue("A{$row}", $no);
                    $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                    $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                    $sheet->setCellValue("D{$row}", $time);
                    $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                    $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                    $sheet->setCellValue("G{$row}", $report->details->first()?->section_name ?? '-');
                    $sheet->setCellValue("H{$row}", $footBasin?->chlorine_std ?? '-');
                    $sheet->setCellValue("I{$row}", $footActualKlorin);
                    $sheet->setCellValue("J{$row}", $footSuhu);
                    $sheet->setCellValue("K{$row}", $handBasin?->chlorine_std ?? '-');
                    $sheet->setCellValue("L{$row}", $handActualKlorin);
                    $sheet->setCellValue("M{$row}", $handSuhu);
                    $sheet->setCellValue("N{$row}", $suhuCuciTangan);
                    $sheet->setCellValue("O{$row}", $suhuCleaning);
                    $sheet->setCellValue("P{$row}", $keterangan ?: '-');
                    $sheet->setCellValue("Q{$row}", $koreksi ?: '-');
                    $sheet->setCellValue("R{$row}", $allVerif ?: '-');
                    

                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->getAlignment()->setHorizontal('center');

                    $row++;
                    $no++;
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