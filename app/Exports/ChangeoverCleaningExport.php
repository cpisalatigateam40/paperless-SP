<?php

namespace App\Exports;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ChangeoverCleaningExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'Changeover Cleaning';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $currentRow = 1;

                // ==========================================================
                // JUDUL
                // ==========================================================
                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue(
                    'A1',
                    'LAPORAN PEMERIKSAAN KEBERSIHAN SETELAH PERGANTIAN PRODUK'
                );

                $sheet->getStyle('A1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(14);

                $sheet->getStyle('A1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:L2');
                $sheet->setCellValue(
                    'A2',
                    'Periode : ' . $this->periodLabel
                );

                $sheet->getStyle('A2')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $currentRow = 4;

                // ==========================================================
                // LOOP REPORT
                // ==========================================================
                foreach ($this->reports as $report) {

                    // ======================================================
                    // HEADER REPORT
                    // ======================================================
                    $sheet->setCellValue(
                        "A{$currentRow}",
                        'Tanggal'
                    );

                    $sheet->setCellValue(
                        "B{$currentRow}",
                        Carbon::parse($report->date)
                            ->translatedFormat('d/m/Y')
                    );

                    $sheet->setCellValue(
                        "D{$currentRow}",
                        'Shift'
                    );

                    $sheet->setCellValue(
                        "E{$currentRow}",
                        $report->shift
                    );

                    $sheet->setCellValue(
                        "G{$currentRow}",
                        'Area'
                    );

                    $sheet->setCellValue(
                        "H{$currentRow}",
                        $report->area->name ?? '-'
                    );

                    $currentRow += 2;

                    // ======================================================
                    // MATRIX
                    // ======================================================
                    $batches = [];
                    $matrix = [];

                    foreach ($report->details as $detail) {

                        $batchKey =
                            $detail->product_uuid .
                            '|' .
                            $detail->time;

                        if (!isset($batches[$batchKey])) {

                            $batches[$batchKey] = [
                                'product_name' =>
                                    $detail->product->product_name ?? '-',

                                'time' =>
                                    $detail->time
                                        ? substr($detail->time, 0, 5)
                                        : '-',
                            ];
                        }

                        $matrix[$detail->item_uuid][$batchKey] = $detail;
                    }

                    $reportItems = $report->details
                        ->groupBy('item_uuid')
                        ->map(fn($group) => $group->first()->item)
                        ->filter()
                        ->sortBy([
                            ['category', 'asc'],
                            ['name', 'asc']
                        ])
                        ->values();

                    // ======================================================
                    // HEADER TABEL
                    // ======================================================
                    $headerRow1 = $currentRow;
                    $headerRow2 = $currentRow + 1;

                    $sheet->mergeCells(
                        "A{$headerRow1}:A{$headerRow2}"
                    );

                    $sheet->mergeCells(
                        "B{$headerRow1}:B{$headerRow2}"
                    );

                    $sheet->mergeCells(
                        "C{$headerRow1}:C{$headerRow2}"
                    );

                    $sheet->setCellValue(
                        "A{$headerRow1}",
                        'No'
                    );

                    $sheet->setCellValue(
                        "B{$headerRow1}",
                        'Kategori'
                    );

                    $sheet->setCellValue(
                        "C{$headerRow1}",
                        'Item'
                    );

                    $colIndex = 4;

                    foreach ($batches as $batch) {

                        $startCol =
                            Coordinate::stringFromColumnIndex($colIndex);

                        $endCol =
                            Coordinate::stringFromColumnIndex($colIndex + 1);

                        $sheet->mergeCells(
                            "{$startCol}{$headerRow1}:{$endCol}{$headerRow1}"
                        );

                        $sheet->setCellValue(
                            "{$startCol}{$headerRow1}",
                            $batch['product_name'] .
                            "\nJam : " .
                            $batch['time']
                        );

                        $sheet->setCellValue(
                            "{$startCol}{$headerRow2}",
                            'X/✓'
                        );

                        $sheet->setCellValue(
                            "{$endCol}{$headerRow2}",
                            'Penjelasan'
                        );

                        $colIndex += 2;
                    }

                    $keteranganCol =
                        Coordinate::stringFromColumnIndex($colIndex);

                    $tindakanCol =
                        Coordinate::stringFromColumnIndex($colIndex + 1);

                    $sheet->mergeCells(
                        "{$keteranganCol}{$headerRow1}:{$keteranganCol}{$headerRow2}"
                    );

                    $sheet->mergeCells(
                        "{$tindakanCol}{$headerRow1}:{$tindakanCol}{$headerRow2}"
                    );

                    $sheet->setCellValue(
                        "{$keteranganCol}{$headerRow1}",
                        'Keterangan'
                    );

                    $sheet->setCellValue(
                        "{$tindakanCol}{$headerRow1}",
                        'Tindakan Koreksi'
                    );

                    // ======================================================
                    // STYLE HEADER
                    // ======================================================
                    $sheet->getStyle(
                        "A{$headerRow1}:{$tindakanCol}{$headerRow2}"
                    )->getFont()->setBold(true);

                    $sheet->getStyle(
                        "A{$headerRow1}:{$tindakanCol}{$headerRow2}"
                    )->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle(
                        "A{$headerRow1}:{$tindakanCol}{$headerRow2}"
                    )->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    $sheet->getStyle(
                        "A{$headerRow1}:{$tindakanCol}{$headerRow2}"
                    )->getAlignment()
                        ->setWrapText(true);

                    // ======================================================
                    // DATA
                    // ======================================================
                    $row = $headerRow2 + 1;

                    $currentCategory = null;
                    $itemNo = 1;

                    foreach ($reportItems as $item) {

                        if ($currentCategory !== $item->category) {

                            $currentCategory = $item->category;
                            $itemNo = 1;

                            $sheet->mergeCells(
                                "A{$row}:{$tindakanCol}{$row}"
                            );

                            $sheet->setCellValue(
                                "A{$row}",
                                strtoupper($currentCategory)
                            );

                            $sheet->getStyle(
                                "A{$row}:{$tindakanCol}{$row}"
                            )->getFont()->setBold(true);

                            $row++;
                        }

                        $sheet->setCellValue(
                            "A{$row}",
                            $itemNo++
                        );

                        $sheet->setCellValue(
                            "B{$row}",
                            $item->category ?? '-'
                        );

                        $sheet->setCellValue(
                            "C{$row}",
                            $item->name
                        );

                        $colIndex = 4;

                        $notes = [];
                        $actions = [];

                        foreach ($batches as $batchKey => $batch) {

                            $cell =
                                $matrix[$item->uuid][$batchKey]
                                ?? null;

                            $resultCol =
                                Coordinate::stringFromColumnIndex($colIndex);

                            $explanationCol =
                                Coordinate::stringFromColumnIndex($colIndex + 1);

                            $sheet->setCellValue(
                                "{$resultCol}{$row}",
                                $cell->result ?? '-'
                            );

                            $sheet->setCellValue(
                                "{$explanationCol}{$row}",
                                $cell->explanation ?? '-'
                            );

                            if ($cell) {

                                if ($cell->notes) {
                                    $notes[] = $cell->notes;
                                }

                                if ($cell->corrective_action) {
                                    $actions[] = $cell->corrective_action;
                                }
                            }

                            $colIndex += 2;
                        }

                        $sheet->setCellValue(
                            "{$keteranganCol}{$row}",
                            count($notes)
                                ? implode('; ', $notes)
                                : '-'
                        );

                        $sheet->setCellValue(
                            "{$tindakanCol}{$row}",
                            count($actions)
                                ? implode('; ', $actions)
                                : '-'
                        );

                        $row++;
                    }

                    if ($reportItems->count() === 0) {

                        $sheet->mergeCells(
                            "A{$row}:{$tindakanCol}{$row}"
                        );

                        $sheet->setCellValue(
                            "A{$row}",
                            'Belum ada detail'
                        );

                        $row++;
                    }

                    // ======================================================
                    // BORDER
                    // ======================================================
                    $sheet->getStyle(
                        "A{$headerRow1}:{$tindakanCol}" . ($row - 1)
                    )->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);

                    // ======================================================
                    // WRAP
                    // ======================================================
                    $sheet->getStyle(
                        "A{$headerRow1}:{$tindakanCol}" . ($row - 1)
                    )->getAlignment()
                        ->setWrapText(true);

                    $currentRow = $row + 3;
                }

                // ==========================================================
                // AUTO WIDTH
                // ==========================================================
                $highestColumn =
                    $sheet->getHighestColumn();

                $highestColumnIndex =
                    Coordinate::columnIndexFromString(
                        $highestColumn
                    );

                for ($i = 1; $i <= $highestColumnIndex; $i++) {

                    $column =
                        Coordinate::stringFromColumnIndex($i);

                    $sheet->getColumnDimension($column)
                        ->setAutoSize(true);
                }
            }
        ];
    }
}