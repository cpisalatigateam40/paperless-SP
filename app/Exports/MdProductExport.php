<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class MdProductExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string
    {
        return 'MD Produk';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Kolom terakhir sekarang Y karena ada Program Number
                $lastCol = 'Y';

                // =========================================================
                // ROW 1 - TITLE
                // =========================================================
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue(
                    'A1',
                    'Verifikasi Kinerja Metal Detector Produk'
                );

                $sheet->getStyle('A1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(13);

                $sheet->getStyle('A1')
                    ->getAlignment()
                    ->setHorizontal('center');


                // =========================================================
                // ROW 2 - PERIOD
                // =========================================================
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue(
                    'A2',
                    'Periode: ' . $this->periodLabel
                );

                $sheet->getStyle('A2')
                    ->getFont()
                    ->setSize(10);

                $sheet->getStyle('A2')
                    ->getAlignment()
                    ->setHorizontal('center');


                // =========================================================
                // ROW 4 - GROUP HEADER
                // =========================================================

                // A-L span row 4-5
                foreach (
                    ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M']
                    as $col
                ) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }


                // Fe 1.5mm : N-P
                $sheet->mergeCells('N4:P4');
                $sheet->setCellValue(
                    'N4',
                    'Speci. Fe 1,5 mm'
                );


                // Non-Fe 2.0mm : Q-S
                $sheet->mergeCells('Q4:S4');
                $sheet->setCellValue(
                    'Q4',
                    'Speci. Non-Fe 2,0 mm'
                );


                // SUS 2.5mm : T-V
                $sheet->mergeCells('T4:V4');
                $sheet->setCellValue(
                    'T4',
                    'Speci. SUS 2,5 mm'
                );


                // W-Y span row 4-5
                foreach (['W', 'X', 'Y'] as $col) {
                    $sheet->mergeCells("{$col}4:{$col}5");
                }


                // Style group header
                foreach (['N4', 'Q4', 'T4'] as $cell) {
                    $sheet->getStyle($cell)
                        ->getFont()
                        ->setBold(true);

                    $sheet->getStyle($cell)
                        ->getAlignment()
                        ->setHorizontal('center')
                        ->setVertical('center');
                }


                // =========================================================
                // ROW 5 - SUB HEADER
                // =========================================================

                $headerLabels = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Waktu Verifikasi',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Merk',
                    'H' => 'Type/Model',
                    'I' => 'No. Series',
                    'J' => 'Nama Produk',
                    'K' => 'Gramase (gr)',
                    'L' => 'Kode Produksi',
                    'M' => 'Nomor Program',
                ];


                foreach ($headerLabels as $col => $label) {

                    $sheet->setCellValue(
                        "{$col}4",
                        $label
                    );

                    $sheet->getStyle("{$col}4")
                        ->getFont()
                        ->setBold(true);

                    $sheet->getStyle("{$col}4")
                        ->getAlignment()
                        ->setHorizontal('center')
                        ->setVertical('center')
                        ->setWrapText(true);
                }


                // =========================================================
                // POSITION HEADER
                // =========================================================

                $posLabels = [
                    'Depan',
                    'Tengah',
                    'Belakang',
                ];

                $posCols = [
                    'fe_1_5mm' => ['N', 'O', 'P'],
                    'non_fe_2mm' => ['Q', 'R', 'S'],
                    'sus_2_5mm' => ['T', 'U', 'V'],
                ];


                foreach ($posCols as $cols) {

                    foreach ($cols as $i => $col) {

                        $sheet->setCellValue(
                            "{$col}5",
                            $posLabels[$i]
                        );

                        $sheet->getStyle("{$col}5")
                            ->getFont()
                            ->setBold(true);

                        $sheet->getStyle("{$col}5")
                            ->getAlignment()
                            ->setHorizontal('center')
                            ->setVertical('center');
                    }
                }


                // =========================================================
                // STATUS / CORRECTIVE / VERIFICATION
                // =========================================================

                $sheet->setCellValue(
                    'W4',
                    'Status (OK/NG)'
                );

                $sheet->setCellValue(
                    'X4',
                    'Tindakan Koreksi'
                );

                $sheet->setCellValue(
                    'Y4',
                    'Keterangan'
                );


                foreach (['W4', 'X4', 'Y4'] as $cell) {

                    $sheet->getStyle($cell)
                        ->getFont()
                        ->setBold(true);

                    $sheet->getStyle($cell)
                        ->getAlignment()
                        ->setHorizontal('center')
                        ->setVertical('center')
                        ->setWrapText(true);
                }


                // =========================================================
                // BORDER ROW 4-5
                // =========================================================

                $sheet->getStyle("A4:{$lastCol}5")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');


                // =========================================================
                // DATA - START ROW 6
                // =========================================================

                $dataRow = 6;
                $no = 1;


                foreach ($this->reports as $report) {

                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2),
                        2,
                        ''
                    );


                    // Metal Detector information
                    $merk = $report->metalDetector->merk ?? '-';

                    $typeModel = $report->metalDetector->type_model ?? '-';

                    $noSeries = $report->metalDetector->no_series ?? '-';


                    foreach ($report->details as $detail) {

                        // =================================================
                        // POSITIONS
                        // [specimen][position] => status
                        // =================================================

                        $pos = [];

                        foreach ($detail->positions as $p) {

                            $pos[$p->specimen][$p->position] =
                                $p->status
                                    ? 'OK'
                                    : 'Tidak OK';
                        }


                        $get = fn($specimen, $position) =>
                            $pos[$specimen][$position] ?? '-';


                        // Detail status
                        $status = $detail->status
                            ? 'OK'
                            : 'NG';


                        // =================================================
                        // BASIC DATA
                        // =================================================

                        $sheet->setCellValue(
                            "A{$dataRow}",
                            $no
                        );

                        $sheet->setCellValue(
                            "B{$dataRow}",
                            Carbon::parse($report->date)
                                ->format('d/m/Y')
                        );

                        $sheet->setCellValue(
                            "C{$dataRow}",
                            $shiftNum ?: ($report->shift ?? '-')
                        );

                        $sheet->setCellValue(
                            "D{$dataRow}",
                            $detail->time
                                ? Carbon::parse($detail->time)
                                    ->format('H:i')
                                : '-'
                        );

                        $sheet->setCellValue(
                            "E{$dataRow}",
                            $report->created_by ?? '-'
                        );

                        $sheet->setCellValue(
                            "F{$dataRow}",
                            $shiftGroup ?: '-'
                        );

                        $sheet->setCellValue(
                            "G{$dataRow}",
                            $merk
                        );

                        $sheet->setCellValue(
                            "H{$dataRow}",
                            $typeModel
                        );

                        $sheet->setCellValue(
                            "I{$dataRow}",
                            $noSeries
                        );

                        $sheet->setCellValue(
                            "J{$dataRow}",
                            $detail->product->product_name ?? '-'
                        );

                        $sheet->setCellValue(
                            "K{$dataRow}",
                            $detail->gramase ?? '-'
                        );

                        // Kode Produksi
                        $sheet->setCellValue(
                            "L{$dataRow}",
                            $detail->production_code ?? '-'
                        );

                        // Program Number
                        $sheet->setCellValue(
                            "M{$dataRow}",
                            $detail->program_number ?? '-'
                        );


                        // =================================================
                        // FE 1.5 MM
                        // =================================================

                        $sheet->setCellValue(
                            "N{$dataRow}",
                            $get('fe_1_5mm', 'd')
                        );

                        $sheet->setCellValue(
                            "O{$dataRow}",
                            $get('fe_1_5mm', 't')
                        );

                        $sheet->setCellValue(
                            "P{$dataRow}",
                            $get('fe_1_5mm', 'b')
                        );


                        // =================================================
                        // NON-FE 2.0 MM
                        // =================================================

                        $sheet->setCellValue(
                            "Q{$dataRow}",
                            $get('non_fe_2mm', 'd')
                        );

                        $sheet->setCellValue(
                            "R{$dataRow}",
                            $get('non_fe_2mm', 't')
                        );

                        $sheet->setCellValue(
                            "S{$dataRow}",
                            $get('non_fe_2mm', 'b')
                        );


                        // =================================================
                        // SUS 2.5 MM
                        // =================================================

                        $sheet->setCellValue(
                            "T{$dataRow}",
                            $get('sus_2_5mm', 'd')
                        );

                        $sheet->setCellValue(
                            "U{$dataRow}",
                            $get('sus_2_5mm', 't')
                        );

                        $sheet->setCellValue(
                            "V{$dataRow}",
                            $get('sus_2_5mm', 'b')
                        );


                        // =================================================
                        // STATUS / CORRECTIVE / VERIFICATION
                        // =================================================

                        $sheet->setCellValue(
                            "W{$dataRow}",
                            $status
                        );

                        $sheet->setCellValue(
                            "X{$dataRow}",
                            $detail->corrective_action ?? '-'
                        );

                        $sheet->setCellValue(
                            "Y{$dataRow}",
                            $detail->verification ?? '-'
                        );


                        // =================================================
                        // ALIGNMENT
                        // =================================================

                        $sheet->getStyle(
                            "A{$dataRow}:{$lastCol}{$dataRow}"
                        )
                            ->getAlignment()
                            ->setHorizontal('center')
                            ->setVertical('center')
                            ->setWrapText(true);


                        // =================================================
                        // BORDER
                        // =================================================

                        $sheet->getStyle(
                            "A{$dataRow}:{$lastCol}{$dataRow}"
                        )
                            ->getBorders()
                            ->getAllBorders()
                            ->setBorderStyle('thin');


                        $dataRow++;
                        $no++;
                    }
                }


                // =========================================================
                // NO DATA
                // =========================================================

                if ($no === 1) {

                    $sheet->mergeCells(
                        "A6:{$lastCol}6"
                    );

                    $sheet->setCellValue(
                        'A6',
                        'Tidak ada data untuk periode yang dipilih.'
                    );

                    $sheet->getStyle('A6')
                        ->getFont()
                        ->setItalic(true);

                    $sheet->getStyle('A6')
                        ->getAlignment()
                        ->setHorizontal('center');
                }


                // =========================================================
                // AUTO WIDTH
                // =========================================================

                $allCols = array_merge(
                    array_keys($headerLabels),
                    [
                        'N',
                        'O',
                        'P',
                        'Q',
                        'R',
                        'S',
                        'T',
                        'U',
                        'V',
                        'W',
                        'X',
                        'Y',
                    ]
                );


                foreach ($allCols as $col) {

                    $sheet->getColumnDimension($col)
                        ->setAutoSize(true);
                }


                // =========================================================
                // ROW HEIGHT
                // =========================================================

                $sheet->getRowDimension(4)
                    ->setRowHeight(20);

                $sheet->getRowDimension(5)
                    ->setRowHeight(30);
            },
        ];
    }
}