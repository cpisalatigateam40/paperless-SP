<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;

class ReCleanlinessExport implements WithMultipleSheets
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function sheets(): array
    {
        return [new ReCleanlinessSheet($this->reports, $this->periodLabel)];
    }
}

class ReCleanlinessSheet implements WithEvents
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
                $sheet->setTitle('Data Kebersihan RE');

                $lastCol = 'K';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI KEBERSIHAN RUANGAN, MESIN & PERALATAN');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => "Area Produksi/Elemen",
                    'H' => 'Kondisi',
                    'I' => 'Keterangan',
                    'J' => 'Tindakan Koreksi',
                    'K' => 'Verifikasi Setelah Tindakan Koreksi',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setWrapText(true);
                    $sheet->getStyle("{$col}4")->getBorders()
                        ->getAllBorders()->setBorderStyle('thin');
                }

                $row = 5;
                $no  = 1;

                foreach ($this->reports as $report) {
                    $date      = Carbon::parse($report->date)->format('d/m/Y');
                    $time      = Carbon::parse($report->created_at)->format('H:i');
                    $createdBy = $report->created_by ?? '-';

                    // shift tidak ada di model, ambil dari property jika ada
                    $shift     = $report->shift ?? '-';
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $shift, 2), 2, ''
                    );

                    // ── SECTION HEADER: RUANGAN ────────────────────────────
                    if ($report->roomDetails->count() > 0) {
                        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                        $sheet->setCellValue("A{$row}", '▶ RUANGAN');
                        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                        $sheet->getStyle("A{$row}")->getFill()
                            ->setFillType('solid')
                            ->getStartColor()->setARGB('FFD9E1F2');
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('left');
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getBorders()
                            ->getAllBorders()->setBorderStyle('thin');
                        $row++;

                        foreach ($report->roomDetails as $detail) {
                            // Kolom G: "Nama Room — Nama Element" atau hanya nama room
                            $areaElemen = $detail->room?->name ?? '-';
                            if ($detail->element?->element_name) {
                                $areaElemen .= ' — ' . $detail->element->element_name;
                            }

                            $this->writeRow($sheet, $row, $lastCol, [
                                'A' => $no,
                                'B' => $date,
                                'C' => $shiftNum ?: $shift,
                                'D' => $time,
                                'E' => $createdBy,
                                'F' => $shiftGroup ?: '-',
                                'G' => $areaElemen,
                                'H' => $detail->condition === 'clean' ? 'Bersih' : 'Kotor',
                                'I' => $detail->notes ?? '-',
                                'J' => $detail->corrective_action ?? '-',
                                'K' => $detail->verification ?? '-',
                            ]);

                            $row++;
                            $no++;
                        }
                    }

                    // ── SECTION HEADER: PERALATAN ──────────────────────────
                    if ($report->equipmentDetails->count() > 0) {
                        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                        $sheet->setCellValue("A{$row}", '▶ MESIN & PERALATAN');
                        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                        $sheet->getStyle("A{$row}")->getFill()
                            ->setFillType('solid')
                            ->getStartColor()->setARGB('FFFCE4D6');
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('left');
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getBorders()
                            ->getAllBorders()->setBorderStyle('thin');
                        $row++;

                        foreach ($report->equipmentDetails as $detail) {
                            $areaElemen = $detail->equipment?->name ?? '-';
                            if ($detail->part?->part_name) {
                                $areaElemen .= ' — ' . $detail->part->part_name;
                            }

                            $this->writeRow($sheet, $row, $lastCol, [
                                'A' => $no,
                                'B' => $date,
                                'C' => $shiftNum ?: $shift,
                                'D' => $time,
                                'E' => $createdBy,
                                'F' => $shiftGroup ?: '-',
                                'G' => $areaElemen,
                                'H' => $detail->condition === 'clean' ? 'Bersih' : 'Kotor',
                                'I' => $detail->notes ?? '-',
                                'J' => $detail->corrective_action ?? '-',
                                'K' => $detail->verification ?? '-',
                            ]);

                            $row++;
                            $no++;
                        }
                    }

                    $row++; // spasi antar report
                }

                if ($no === 1) {
                    $sheet->mergeCells("A5:{$lastCol}5");
                    $sheet->setCellValue('A5', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A5')->getFont()->setItalic(true);
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                }

                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(35);
            },
        ];
    }

    private function writeRow($sheet, int $row, string $lastCol, array $data): void
    {
        foreach ($data as $col => $value) {
            $sheet->setCellValue("{$col}{$row}", $value);
        }
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")
            ->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getBorders()
            ->getAllBorders()->setBorderStyle('thin');
    }
}