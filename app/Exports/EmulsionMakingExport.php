<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;

class EmulsionMakingExport implements WithMultipleSheets
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function sheets(): array
    {
        return [
            new EmulsionMakingDataSheet($this->reports, $this->periodLabel),
        ];
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// SHEET — kolom persis sama dengan template import
// ═══════════════════════════════════════════════════════════════════════════════
// Template import:
// A=Tanggal, B=Shift, C=Time, D=QC, E=Group, F=Jenis Emulsi, G=Kode Prod,
// H=Bahan, I=Berat, J=Suhu, K=Kesesuaian formula,
// L=Awal Proses, M=Akhir Proses, N=Warna Emulsi, O=Tekstur Emulsi,
// P=Suhu Emulsi, Q=Hasil Emulsi
// ═══════════════════════════════════════════════════════════════════════════════

class EmulsionMakingDataSheet implements WithEvents
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
                $sheet->setTitle('Data Emulsi');

                // ── Judul ──────────────────────────────────────────────────
                $sheet->mergeCells('A1:R1');
                $sheet->setCellValue('A1', 'LAPORAN PEMBUATAN EMULSI');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells('A2:R2');
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Header (row 4) — persis urutan template import ─────────
                $headers = [
                    'A' => 'No',
                    'B' => 'Tanggal',
                    'C' => 'Shift',
                    'D' => 'Time',
                    'E' => 'QC',
                    'F' => 'Group',
                    'G' => 'Jenis Emulsi',
                    'H' => 'Kode Prod',
                    'I' => 'Bahan',
                    'J' => 'Berat',
                    'K' => 'Suhu',
                    'L' => 'Kesesuaian Formula',
                    'M' => 'Awal Proses',
                    'N' => 'Akhir Proses',
                    'O' => 'Warna Emulsi',
                    'P' => 'Tekstur Emulsi',
                    'Q' => 'Suhu Emulsi',
                    'R' => 'Hasil Emulsi',
                ];

                foreach ($headers as $col => $label) {
                    $sheet->setCellValue("{$col}4", $label);
                    $sheet->getStyle("{$col}4")->getFont()->setBold(true);
                    $sheet->getStyle("{$col}4")->getAlignment()
                        ->setHorizontal('center')->setWrapText(true);
                }

                // ── Data (mulai row 5) ─────────────────────────────────────
                // Satu report bisa punya banyak details (bahan) dan banyak agings.
                // Tiap baris = satu detail bahan, aging diulang per baris detail.
                // Jika jumlah detail != aging, kolom aging diisi '-' jika tidak ada pasangan.
                $row = 5;
                $no  = 1;

                foreach ($this->reports as $report) {
                    if (!$report->header) continue;

                    $header  = $report->header;
                    $details = $header->details;
                    $agings  = $header->agings;
                    $maxRows = max($details->count(), $agings->count(), 1);

                    [$shiftNum, $shiftGroup] = array_pad(
                        explode('-', $report->shift ?? '', 2), 2, ''
                    );

                    for ($i = 0; $i < $maxRows; $i++) {
                        $detail = $details->get($i);
                        $aging  = $agings->get($i);

                        $materialName = '-';
                        if ($detail) {
                            $materialName = $detail->material_type === 'premix'
                                ? ($detail->premix->name ?? '-')
                                : ($detail->rawMaterial->material_name ?? '-');
                        }

                        $sheet->setCellValue("A{$row}", $no);
                        $sheet->setCellValue("B{$row}", Carbon::parse($report->date)->format('d/m/Y'));
                        $sheet->setCellValue("C{$row}", $shiftNum ?: ($report->shift ?? '-'));
                        $time = ($aging?->start_aging && $aging?->finish_aging) ? $aging->start_aging . ' - ' . $aging->finish_aging : '-';
                        $sheet->setCellValue("D{$row}", $time);
                        $sheet->setCellValue("E{$row}", $report->created_by ?? '-');
                        $sheet->setCellValue("F{$row}", $shiftGroup ?: '-');
                        $sheet->setCellValue("G{$row}", $header->emulsion_type ?? '-');
                        $sheet->setCellValue("H{$row}", $header->production_code ?? '-');
                        $sheet->setCellValue("I{$row}", $detail ? $materialName : '-');
                        $sheet->setCellValue("J{$row}", $detail?->weight ?? '-');
                        $sheet->setCellValue("K{$row}", $detail?->temperature ?? '-');
                        $sheet->setCellValue("L{$row}", $detail?->conformity ?? '-');
                        $sheet->setCellValue("M{$row}", $aging?->start_aging ?? '-');
                        $sheet->setCellValue("N{$row}", $aging?->finish_aging ?? '-');
                        $sheet->setCellValue("O{$row}", $aging?->sensory_color ?? '-');
                        $sheet->setCellValue("P{$row}", $aging?->sensory_texture ?? '-');
                        $sheet->setCellValue("Q{$row}", $aging?->temp_after ?? '-');
                        $sheet->setCellValue("R{$row}", $aging?->emulsion_result ?? '-');

                        $sheet->getStyle("A{$row}:R{$row}")
                            ->getAlignment()->setHorizontal('center');

                        $row++;
                        $no++;
                    }
                }

                if ($no === 1) {
                    $sheet->mergeCells('A5:R5');
                    $sheet->setCellValue('A5', 'Tidak ada data untuk periode yang dipilih.');
                    $sheet->getStyle('A5')->getFont()->setItalic(true);
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                    $row++;
                }

                // ── Border & auto width ────────────────────────────────────
                $sheet->getStyle("A4:R" . ($row - 1))->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                foreach (array_keys($headers) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(4)->setRowHeight(30);
            },
        ];
    }
}