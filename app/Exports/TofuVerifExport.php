<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class TofuVerifExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Verifikasi Tofu'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Kolom:
                // A-G   : No, Tanggal, Shift, Time, QC, Group, Kode Produksi
                // H-P   : Hasil Pemeriksaan Berat (Under/Standard/Over × Turus/Jumlah/%)
                // Q-AE  : Hasil Pemeriksaan Defect (5 type × Turus/Jumlah/%)
                $lastCol = 'AE';

                // ── Judul ──────────────────────────────────────────────────
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN VERIFIKASI PRODUK TOFU');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Row 4: Group header (colspan) ──────────────────────────
                // A-G span row 4-5 (merge vertikal)
                $headerLabels = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Kode Produksi',
                ];

                foreach ($headerLabels as $col => $label) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                    $sheet->setCellValue("{$col}4", $label);  // set di row 4 (cell pertama merge)
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                // H-P : Hasil Pemeriksaan Berat (9 kolom)
                $sheet->mergeCells('H4:P4');
                $sheet->setCellValue('H4', 'Hasil Pemeriksaan Berat');

                // Q-AE : Hasil Pemeriksaan Defect (15 kolom)
                $sheet->mergeCells('Q4:AE4');
                $sheet->setCellValue('Q4', 'Hasil Pemeriksaan Defect');

                // Style group header
                foreach (['H4', 'Q4'] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal('center')->setVertical('center');
                }

                // ── Row 5: Sub-header kolom ────────────────────────────────
                $row5headers = [
                    // Berat
                    'H' => "Under\n(<11gr)\nTurus",
                    'I' => "Under\n(<11gr)\nJumlah",
                    'J' => "Under\n(<11gr)\n%",
                    'K' => "Standar\n(11-13gr)\nTurus",
                    'L' => "Standar\n(11-13gr)\nJumlah",
                    'M' => "Standar\n(11-13gr)\n%",
                    'N' => "Over\n(>13gr)\nTurus",
                    'O' => "Over\n(>13gr)\nJumlah",
                    'P' => "Over\n(>13gr)\n%",
                    // Defect
                    'Q' => "Berlubang\nTurus",
                    'R' => "Berlubang\nJumlah",
                    'S' => "Berlubang\n%",
                    'T' => "Noda\nTurus",
                    'U' => "Noda\nJumlah",
                    'V' => "Noda\n%",
                    'W' => "Tdk Bulat\nSimetris\nTurus",
                    'X' => "Tdk Bulat\nSimetris\nJumlah",
                    'Y' => "Tdk Bulat\nSimetris\n%",
                    'Z' => "Lain-lain\nTurus",
                    'AA' => "Lain-lain\nJumlah",
                    'AB' => "Lain-lain\n%",
                    'AC' => "Produk\nBagus\nTurus",
                    'AD' => "Produk\nBagus\nJumlah",
                    'AE' => "Produk\nBagus\n%",
                ];

                foreach ($row5headers as $col => $label) {
                    $sheet->setCellValue("{$col}5", $label);
                    $sheet->getStyle("{$col}5")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}5")->getAlignment()
                        ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                }

                // Border rows 4-5
                $sheet->getStyle("A4:{$lastCol}5")->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                // ── Data (mulai row 6) ─────────────────────────────────────
                $dataRow = 6;
                $no      = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    // weight & defect langsung dari report (tidak ada product_info_uuid)
                    $weights = $report->weightVerifs->keyBy('weight_category');
                    $defects = $report->defectVerifs->keyBy('defect_type');

                    foreach ($report->productInfos as $productInfo) {

                        $get = fn($col, $field) => $col?->$field ?? '-';

                        $sheet->setCellValue("A{$dataRow}", $no);
                        $sheet->setCellValue("B{$dataRow}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$dataRow}", $shiftNum ?: ($report->shift ?? '-'));
                        $sheet->setCellValue("D{$dataRow}", Carbon::parse($report->created_at)->format('H:i'));
                        $sheet->setCellValue("E{$dataRow}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$dataRow}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$dataRow}", $productInfo->production_code ?? '-');
                        // Berat
                        $sheet->setCellValue("H{$dataRow}", $get($weights->get('under'),    'turus'));
                        $sheet->setCellValue("I{$dataRow}", $get($weights->get('under'),    'total'));
                        $sheet->setCellValue("J{$dataRow}", $get($weights->get('under'),    'percentage'));
                        $sheet->setCellValue("K{$dataRow}", $get($weights->get('standard'), 'turus'));
                        $sheet->setCellValue("L{$dataRow}", $get($weights->get('standard'), 'total'));
                        $sheet->setCellValue("M{$dataRow}", $get($weights->get('standard'), 'percentage'));
                        $sheet->setCellValue("N{$dataRow}", $get($weights->get('over'),     'turus'));
                        $sheet->setCellValue("O{$dataRow}", $get($weights->get('over'),     'total'));
                        $sheet->setCellValue("P{$dataRow}", $get($weights->get('over'),     'percentage'));
                        // Defect
                        $sheet->setCellValue("Q{$dataRow}", $get($defects->get('hole'),       'turus'));
                        $sheet->setCellValue("R{$dataRow}", $get($defects->get('hole'),       'total'));
                        $sheet->setCellValue("S{$dataRow}", $get($defects->get('hole'),       'percentage'));
                        $sheet->setCellValue("T{$dataRow}", $get($defects->get('stain'),      'turus'));
                        $sheet->setCellValue("U{$dataRow}", $get($defects->get('stain'),      'total'));
                        $sheet->setCellValue("V{$dataRow}", $get($defects->get('stain'),      'percentage'));
                        $sheet->setCellValue("W{$dataRow}", $get($defects->get('asymmetry'),  'turus'));
                        $sheet->setCellValue("X{$dataRow}", $get($defects->get('asymmetry'),  'total'));
                        $sheet->setCellValue("Y{$dataRow}", $get($defects->get('asymmetry'),  'percentage'));
                        $sheet->setCellValue("Z{$dataRow}", $get($defects->get('other'),      'turus'));
                        $sheet->setCellValue("AA{$dataRow}", $get($defects->get('other'),     'total'));
                        $sheet->setCellValue("AB{$dataRow}", $get($defects->get('other'),     'percentage'));
                        $sheet->setCellValue("AC{$dataRow}", $get($defects->get('good'),      'turus'));
                        $sheet->setCellValue("AD{$dataRow}", $get($defects->get('good'),      'total'));
                        $sheet->setCellValue("AE{$dataRow}", $get($defects->get('good'),      'percentage'));

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

                // ── Auto width ─────────────────────────────────────────────
                foreach (array_keys($row5headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(45);
            },
        ];
    }
}