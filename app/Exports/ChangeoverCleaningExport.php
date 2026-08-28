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

                // ==========================================================
                // JUDUL
                // ==========================================================
                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', 'Pemeriksaan Kebersihan Setelah Change-Over');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:L2');
                $sheet->setCellValue('A2', 'Periode : ' . $this->periodLabel);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $currentRow = 4;

                foreach ($this->reports as $report) {

                    $isLegacy = $report->details->whereNotNull('result')->isNotEmpty();

                    // Header laporan
                    $sheet->setCellValue("A{$currentRow}", 'Tanggal');
                    $sheet->setCellValue("B{$currentRow}", Carbon::parse($report->date)->translatedFormat('d/m/Y'));
                    $sheet->setCellValue("D{$currentRow}", 'Shift');
                    $sheet->setCellValue("E{$currentRow}", $report->shift);

                    if ($isLegacy) {
                        $sheet->setCellValue("G{$currentRow}", 'Area');
                        $sheet->setCellValue("H{$currentRow}", $report->area->name ?? '-');
                    } else {
                        $sectionNames = $report->details
                            ->where('group', 'mesin_peralatan')
                            ->pluck('item.section.section_name')
                            ->filter()
                            ->unique()
                            ->values()
                            ->implode(', ');

                        $sheet->setCellValue("G{$currentRow}", 'Section');
                        $sheet->setCellValue("H{$currentRow}", $sectionNames ?: '-');
                    }

                    $currentRow += 2;

                    $currentRow = $isLegacy
                        ? $this->renderLegacyReport($sheet, $report, $currentRow)
                        : $this->renderNewReport($sheet, $report, $currentRow);

                    $currentRow += 3;
                }

                // AUTO WIDTH
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

                for ($i = 1; $i <= $highestColumnIndex; $i++) {
                    $column = Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            }
        ];
    }

    /**
     * Layout LAMA: matriks kategori x batch, kolom result/explanation per batch.
     * Dipakai untuk laporan yang dibuat sebelum perombakan form (masih ada kolom 'result').
     */
    private function renderLegacyReport($sheet, $report, int $currentRow): int
    {
        $batches = [];
        $matrix = [];

        foreach ($report->details as $detail) {
            $batchKey = $detail->product_uuid . '|' . $detail->time;

            if (!isset($batches[$batchKey])) {
                $batches[$batchKey] = [
                    'product_name' => $detail->product->product_name ?? '-',
                    'time'         => $detail->time ? substr($detail->time, 0, 5) : '-',
                ];
            }

            $matrix[$detail->item_uuid][$batchKey] = $detail;
        }

        $reportItems = $report->details
            ->groupBy('item_uuid')
            ->map(fn($group) => $group->first()->item)
            ->filter()
            ->sortBy([['category', 'asc'], ['name', 'asc']])
            ->values();

        $headerRow1 = $currentRow;
        $headerRow2 = $currentRow + 1;

        $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");
        $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}");
        $sheet->mergeCells("C{$headerRow1}:C{$headerRow2}");
        $sheet->setCellValue("A{$headerRow1}", 'No');
        $sheet->setCellValue("B{$headerRow1}", 'Kategori');
        $sheet->setCellValue("C{$headerRow1}", 'Item');

        $colIndex = 4;

        foreach ($batches as $batch) {
            $startCol = Coordinate::stringFromColumnIndex($colIndex);
            $endCol = Coordinate::stringFromColumnIndex($colIndex + 1);

            $sheet->mergeCells("{$startCol}{$headerRow1}:{$endCol}{$headerRow1}");
            $sheet->setCellValue("{$startCol}{$headerRow1}", $batch['product_name'] . "\nJam : " . $batch['time']);
            $sheet->setCellValue("{$startCol}{$headerRow2}", 'X/✓');
            $sheet->setCellValue("{$endCol}{$headerRow2}", 'Penjelasan');

            $colIndex += 2;
        }

        $keteranganCol = Coordinate::stringFromColumnIndex($colIndex);
        $tindakanCol = Coordinate::stringFromColumnIndex($colIndex + 1);

        $sheet->mergeCells("{$keteranganCol}{$headerRow1}:{$keteranganCol}{$headerRow2}");
        $sheet->mergeCells("{$tindakanCol}{$headerRow1}:{$tindakanCol}{$headerRow2}");
        $sheet->setCellValue("{$keteranganCol}{$headerRow1}", 'Keterangan');
        $sheet->setCellValue("{$tindakanCol}{$headerRow1}", 'Tindakan Koreksi');

        $sheet->getStyle("A{$headerRow1}:{$tindakanCol}{$headerRow2}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow1}:{$tindakanCol}{$headerRow2}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $row = $headerRow2 + 1;
        $currentCategory = null;
        $itemNo = 1;

        foreach ($reportItems as $item) {
            if ($currentCategory !== $item->category) {
                $currentCategory = $item->category;
                $itemNo = 1;

                $sheet->mergeCells("A{$row}:{$tindakanCol}{$row}");
                $sheet->setCellValue("A{$row}", strtoupper($currentCategory));
                $sheet->getStyle("A{$row}:{$tindakanCol}{$row}")->getFont()->setBold(true);

                $row++;
            }

            $sheet->setCellValue("A{$row}", $itemNo++);
            $sheet->setCellValue("B{$row}", $item->category ?? '-');
            $sheet->setCellValue("C{$row}", $item->name);

            $colIndex = 4;
            $notes = [];
            $actions = [];

            foreach ($batches as $batchKey => $batch) {
                $cell = $matrix[$item->uuid][$batchKey] ?? null;

                $resultCol = Coordinate::stringFromColumnIndex($colIndex);
                $explanationCol = Coordinate::stringFromColumnIndex($colIndex + 1);

                $sheet->setCellValue("{$resultCol}{$row}", $cell->result ?? '-');
                $sheet->setCellValue("{$explanationCol}{$row}", $cell->explanation ?? '-');

                if ($cell) {
                    if ($cell->notes) $notes[] = $cell->notes;
                    if ($cell->corrective_action) $actions[] = $cell->corrective_action;
                }

                $colIndex += 2;
            }

            $sheet->setCellValue("{$keteranganCol}{$row}", count($notes) ? implode('; ', $notes) : '-');
            $sheet->setCellValue("{$tindakanCol}{$row}", count($actions) ? implode('; ', $actions) : '-');

            $row++;
        }

        if ($reportItems->count() === 0) {
            $sheet->mergeCells("A{$row}:{$tindakanCol}{$row}");
            $sheet->setCellValue("A{$row}", 'Belum ada detail');
            $row++;
        }

        $sheet->getStyle("A{$headerRow1}:{$tindakanCol}" . ($row - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle("A{$headerRow1}:{$tindakanCol}" . ($row - 1))
            ->getAlignment()->setWrapText(true);

        return $row;
    }

    /**
     * Layout BARU: per-batch (produk + kode produksi), 3 tabel grup
     * (Sisa Bahan, Mesin & Peralatan, Kondisi Ruangan), kolom skor tunggal 1-8.
     */
    private function renderNewReport($sheet, $report, int $currentRow): int
    {
        $groupLabels = [
            'sisa_bahan'      => 'Sisa Bahan dan Kemasan',
            'mesin_peralatan' => 'Mesin dan Peralatan',
            'kondisi_ruangan' => 'Kondisi Ruangan',
        ];

        $pages = [];

        foreach ($report->details as $d) {
            $batchKey = $d->product_uuid . '|' . $d->time;

            if (!isset($pages[$batchKey])) {
                $pages[$batchKey] = [
                    'product_name'    => $d->product->product_name ?? '-',
                    'time'            => $d->time ? substr($d->time, 0, 5) : '-',
                    'production_code' => $d->production_code ?? '-',
                    'sisa_bahan'      => [],
                    'mesin_peralatan' => [],
                    'kondisi_ruangan' => [],
                ];
            }

            $pages[$batchKey][$d->group][] = [
                'name'              => $d->item_name ?? ($d->item->name ?? '-'),
                'score'             => $d->score,
                'notes'             => $d->notes,
                'corrective_action' => $d->corrective_action,
            ];
        }

        $row = $currentRow;

        // Kolom tetap: A=No, B=Item, C=Kriteria, D=Tindakan Koreksi, E=Keterangan
        $lastCol = 'E';

        foreach ($pages as $page) {

            // Info batch: Produk, Kode Produksi, Jam
            $sheet->setCellValue("A{$row}", 'Produk');
            $sheet->setCellValue("B{$row}", $page['product_name']);
            $sheet->setCellValue("C{$row}", 'Kode Produksi');
            $sheet->setCellValue("D{$row}", $page['production_code']);
            $sheet->setCellValue("E{$row}", 'Jam : ' . $page['time']);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);

            $row += 2;

            foreach ($groupLabels as $groupKey => $groupLabel) {

                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->setCellValue("A{$row}", strtoupper($groupLabel));
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);
                $row++;

                $headerRow = $row;
                $sheet->setCellValue("A{$headerRow}", 'No');
                $sheet->setCellValue("B{$headerRow}", 'Item');
                $sheet->setCellValue("C{$headerRow}", 'Kriteria');
                $sheet->setCellValue("D{$headerRow}", 'Tindakan Koreksi');
                $sheet->setCellValue("E{$headerRow}", 'Keterangan');
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row++;

                $rows = $page[$groupKey];

                if (empty($rows)) {
                    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                    $sheet->setCellValue("A{$row}", 'Belum ada data ' . strtolower($groupLabel));
                    $row++;
                } else {
                    foreach ($rows as $i => $itemRow) {
                        $sheet->setCellValue("A{$row}", $i + 1);
                        $sheet->setCellValue("B{$row}", $itemRow['name']);
                        $sheet->setCellValue("C{$row}", $itemRow['score'] ?? '-');
                        $sheet->setCellValue("D{$row}", $itemRow['corrective_action'] ?? '-');
                        $sheet->setCellValue("E{$row}", $itemRow['notes'] ?? '-');
                        $row++;
                    }
                }

                $row++; // spasi antar grup
            }

            $row++; // spasi antar batch
        }

        $sheet->getStyle("A{$currentRow}:{$lastCol}" . ($row - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle("A{$currentRow}:{$lastCol}" . ($row - 1))
            ->getAlignment()->setWrapText(true);

        return $row;
    }
}