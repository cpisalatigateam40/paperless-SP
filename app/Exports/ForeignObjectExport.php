<?php

namespace App\Exports;

use App\Models\DetailForeignObject;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class ForeignObjectExport implements WithEvents, WithTitle
{
    // key => label header
    private const HEADERS = [
        'no'                   => 'No',
        'date'                 => 'Tanggal',
        'shift'                => 'Shift',
        'time'                 => 'Time',
        'qc'                   => 'QC',
        'group'                => 'Group',
        'section'              => 'Section',
        'product'              => 'Product / RM',
        'item_type'            => 'Tipe',
        'gramase'              => 'Gramase (gr)',
        'production_code'      => 'Kode Prod',
        'process_stage'        => 'Process Stage',
        'line'                 => 'Line',
        'category'             => 'Category',
        'type'                 => 'Type',
        'size_quantity'        => 'Size / Quantity',
        'description'          => 'Deskripsi',
        'product_status'       => 'Product Status',
        'affected_quantity'    => 'Affected Qty',
        'disposition'          => 'Disposition',
        'investigation'        => 'Investigation',
        'immediate_correction' => 'Immediate Correction',
        'corrective_action'    => 'Corrective Action',
        'evidence'             => 'Evidence',
        'notes'                => 'Keterangan (Caption)',
        'report_notes'         => 'Catatan Laporan',
    ];

    // Kolom teks: rata kiri
    private const LEFT_COLS = [
        'section', 'product', 'process_stage', 'line', 'category', 'type',
        'size_quantity', 'description', 'investigation', 'immediate_correction',
        'notes', 'report_notes',
    ];

    // Kolom teks panjang: lebar tetap + wrap
    private const WIDE_COLS = [
        'description', 'investigation', 'immediate_correction', 'notes', 'report_notes',
    ];

    // Thumbnail evidence: 3 foto per baris di dalam sel
    private const THUMB_W = 95;
    private const THUMB_H = 68;
    private const THUMBS_PER_LINE = 3;
    private const LINE_PX = 74;   // jarak vertikal antar baris foto (px)
    private const LINE_PT = 56;   // tinggi baris Excel per baris foto (point)

    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Foreign Object'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $keys    = array_keys(self::HEADERS);
                $lastCol = Coordinate::stringFromColumnIndex(count($keys));
                $colOf   = fn (string $key) => Coordinate::stringFromColumnIndex(array_search($key, $keys) + 1);

                // ===== JUDUL =====
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Foreign Object Contamination Report');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ===== HEADER =====
                foreach ($keys as $i => $key) {
                    $col = Coordinate::stringFromColumnIndex($i + 1);
                    $sheet->setCellValue("{$col}4", self::HEADERS[$key]);
                }

                $sheet->getStyle("A4:{$lastCol}4")->getFont()->setBold(true);
                $sheet->getStyle("A4:{$lastCol}4")->getAlignment()
                    ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                $sheet->getStyle("A4:{$lastCol}4")->getFill()
                    ->setFillType('solid')->getStartColor()->setRGB('E9EDF5');
                $sheet->getRowDimension(4)->setRowHeight(30);

                // ===== DATA =====
                $row = 5;
                $no  = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    foreach ($report->details as $detail) {
                        $values = $this->rowValues($report, $detail, $no, $shiftNum, $shiftGroup);

                        foreach ($keys as $i => $key) {
                            $col = Coordinate::stringFromColumnIndex($i + 1);
                            $sheet->setCellValue("{$col}{$row}", $values[$key]);
                        }

                        // Default: tengah, atas
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()
                            ->setHorizontal('center')->setVertical('top');

                        // Kolom teks rata kiri
                        foreach (self::LEFT_COLS as $key) {
                            $sheet->getStyle($colOf($key) . $row)->getAlignment()->setHorizontal('left');
                        }

                        // Kolom teks panjang: wrap
                        foreach (self::WIDE_COLS as $key) {
                            $sheet->getStyle($colOf($key) . $row)->getAlignment()->setWrapText(true);
                        }

                        // Evidence: tempel semua thumbnail (maks. 3 per baris foto)
                        $evidencePaths = array_values(array_filter((array) $detail->evidence, fn ($v) => filled($v)));
                        $photoLines    = $this->placeEvidence($sheet, $colOf('evidence') . $row, $evidencePaths);

                        // Tinggi baris: cukup untuk foto dan/atau investigation multi-poin
                        $textLines = substr_count((string) $values['investigation'], "\n") + 1;
                        $height = max(
                            $photoLines * self::LINE_PT,
                            $textLines > 1 ? $textLines * 14 : 0
                        );
                        if ($height > 0) {
                            $sheet->getRowDimension($row)->setRowHeight($height);
                        }

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

                // ===== STYLE AKHIR =====
                $sheet->getStyle("A4:{$lastCol}" . ($row - 1))->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                foreach ($keys as $i => $key) {
                    $dim = $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1));

                    if ($key === 'evidence') {
                        $dim->setAutoSize(false)->setWidth(46);
                    } elseif (in_array($key, self::WIDE_COLS, true)) {
                        $dim->setAutoSize(false)->setWidth(30);
                    } else {
                        $dim->setAutoSize(true);
                    }
                }

                // Header tetap terlihat saat scroll + filter di tiap kolom
                $sheet->freezePane('A5');
                if ($no > 1) {
                    $sheet->setAutoFilter("A4:{$lastCol}" . ($row - 1));
                }
            },
        ];
    }

    private function rowValues($report, $detail, int $no, string $shiftNum, string $shiftGroup): array
    {
        $actions = DetailForeignObject::ACTION_OPTIONS;
        $type    = $detail->contaminationType;

        // Investigation: bernomor kalau lebih dari 1; data lama jatuh ke kolom legacy
        $items = array_values(array_filter((array) $detail->investigation, fn ($v) => filled($v)));
        if (empty($items)) {
            $items = array_values(array_filter([$detail->analysis_stage, $detail->contaminant_origin]));
        }
        $investigation = match (count($items)) {
            0       => '-',
            1       => $items[0],
            default => collect($items)->map(fn ($v, $i) => ($i + 1) . '. ' . $v)->implode("\n"),
        };

        $gramase = !empty($detail->gramase) ? $detail->gramase : ($detail->product->nett_weight ?? null);

        return [
            'no'                   => $no,
            'date'                 => Carbon::parse($report->date)->format('d/m/Y'),
            'shift'                => $shiftNum ?: ($report->shift ?? '-'),
            'time'                 => $detail->time ? substr($detail->time, 0, 5) : '-',
            'qc'                   => $report->created_by ?? '-',
            'group'                => $shiftGroup ?: '-',
            'section'              => $report->section->section_name ?? '-',
            'product'              => $detail->item_name ?? '-',
            'item_type'            => $detail->item_type_label ?? '-',
            'gramase'              => is_numeric($gramase) ? (float) $gramase : '-',
            'production_code'      => $detail->production_code ?: '-',
            'process_stage'        => $detail->process_stage ?: '-',
            'line'                 => $detail->line ?: '-',
            // data lama: teks bebas masuk ke kolom Type
            'category'             => $type ? ($type->category->name ?? '-') : '-',
            'type'                 => $type ? $type->name : ($detail->contaminant_type ?: '-'),
            'size_quantity'        => $detail->size_quantity ?: '-',
            'description'          => $detail->description ?: '-',
            'product_status'       => $actions[$detail->product_status] ?? '-',
            'affected_quantity'    => $detail->affected_quantity ?: '-',
            'disposition'          => $actions[$detail->disposition] ?? '-',
            'investigation'        => $investigation,
            'immediate_correction' => $detail->immediate_correction ?: '-',
            'corrective_action'    => $actions[$detail->corrective_action] ?? '-',
            'evidence'             => '',   // gambar ditempel terpisah lewat placeEvidence()
            'notes'                => $detail->notes ?: '-',
            'report_notes'         => $report->notes ?: '-',
        ];
    }

    /**
     * Tempel thumbnail semua evidence ke sel. Return jumlah baris foto yang terpakai (0 = tidak ada foto).
     */
    private function placeEvidence($sheet, string $cell, array $paths): int
    {
        $placed = 0;

        foreach ($paths as $path) {
            $full  = storage_path('app/public/' . $path);
            $thumb = is_file($full) ? $this->thumbnail($full, self::THUMB_W, self::THUMB_H) : null;

            if (!$thumb) {
                continue;
            }

            $drawing = new MemoryDrawing();
            $drawing->setImageResource($thumb);
            $drawing->setRenderingFunction(MemoryDrawing::RENDERING_JPEG);
            $drawing->setMimeType(MemoryDrawing::MIMETYPE_DEFAULT);
            $drawing->setCoordinates($cell);
            $drawing->setOffsetX(4 + ($placed % self::THUMBS_PER_LINE) * (self::THUMB_W + 6));
            $drawing->setOffsetY(4 + intdiv($placed, self::THUMBS_PER_LINE) * self::LINE_PX);
            $drawing->setWorksheet($sheet);

            $placed++;
        }

        if ($placed === 0) {
            $sheet->setCellValue($cell, '-');
            return 0;
        }

        return (int) ceil($placed / self::THUMBS_PER_LINE);
    }

    /**
     * Perkecil gambar (jpg/png) agar file Excel tidak membengkak.
     */
    private function thumbnail(string $path, int $maxW, int $maxH)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $src = match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png'         => @imagecreatefrompng($path),
            default       => false,
        };

        if (!$src) {
            return null;
        }

        $w     = imagesx($src);
        $h     = imagesy($src);
        $ratio = min($maxW / $w, $maxH / $h, 1);
        $newW  = max(1, (int) round($w * $ratio));
        $newH  = max(1, (int) round($h * $ratio));

        $dst = imagecreatetruecolor($newW, $newH);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255)); // latar putih untuk PNG transparan
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        imagedestroy($src);

        return $dst;
    }
}