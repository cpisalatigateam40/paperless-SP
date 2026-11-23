<?php

namespace App\Exports;

use App\Models\ReportMdReject;
use App\Models\ReportMdRejectDetail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportMdRejectExport implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    private const HEADER_ROW = 4;
    private const LAST_COL   = 'T';

    private int $dataCount = 0;

    public function __construct(
        private Collection $reports,
        private string $periodLabel
    ) {}

    public function title(): string
    {
        return 'Reject Metal Detector';
    }

    public function array(): array
    {
        $result = [
            ['PEMERIKSAAN PRODUK REJECT METAL DETECTOR'],
            ['Periode: ' . $this->periodLabel],
            [],
            [
                'No', 'Tanggal', 'Shift', 'Line', 'Area',
                'Fe 1.5 mm', 'Non-Fe 2 mm', 'SUS 2.5 mm',
                'Jam', 'Nama Produk', 'Kode Produksi', 'Jumlah Reject (pcs/pack)',
                'Hasil Pengecekan Ulang', 'Kontaminasi Ditemukan', 'Dugaan Penyebab',
                'Tindakan Perbaikan / Korektif', 'Disposisi Produk',
                'Diperiksa Oleh', 'Diketahui Oleh', 'Disetujui Oleh',
            ],
        ];

        $resultLabel = ['detected' => 'Terdeteksi', 'not_detected' => 'Tidak Terdeteksi'];
        $causeLabels = ReportMdRejectDetail::CAUSES;
        $dispLabels  = ReportMdRejectDetail::DISPOSITIONS;

        $no = 0;

        foreach ($this->reports as $report) {
            // Hasil verifikasi test piece (sama di setiap baris laporan)
            $verif = [];
            foreach (ReportMdReject::TEST_PIECES as $piece) {
                $v = $report->verifications->firstWhere('test_piece', $piece);
                $text = $resultLabel[$v->result ?? ''] ?? '-';
                if ($v && filled($v->notes)) {
                    $text .= " ({$v->notes})";
                }
                $verif[] = $text;
            }

            $base = [
                $report->date->format('d/m/Y'),
                $report->shift,
                $report->line,
                optional($report->area)->name,
            ];

            $signers = [$report->created_by, $report->known_by, $report->approved_by];

            // Laporan tanpa produk reject tetap muncul satu baris
            $details = $report->details->isNotEmpty() ? $report->details : collect([null]);

            foreach ($details as $d) {
                $no++;

                $causes = collect($d->suspected_causes ?? [])->map(function ($key) use ($causeLabels, $d) {
                    return $key === 'other'
                        ? 'Lainnya: ' . ($d->suspected_cause_other ?: '-')
                        : ($causeLabels[$key] ?? $key);
                })->implode('; ');

                $result[] = array_merge(
                    [$no],
                    $base,
                    $verif,
                    [
                        $d ? substr($d->time, 0, 5) : '-',
                        optional(optional($d)->product)->product_name ?? '-',
                        $d->production_code ?? '-',
                        $d->reject_qty ?? '-',
                        $d ? ($resultLabel[$d->recheck_result] ?? '-') : '-',
                        $d ? (is_null($d->contamination_found) ? '-' : ($d->contamination_found ? 'Ada' : 'Tidak')) : '-',
                        $causes ?: '-',
                        $d->corrective_action ?? '-',
                        $d ? ($dispLabels[$d->disposition] ?? '-') : '-',
                    ],
                    $signers
                );
            }
        }

        $this->dataCount = $no;

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        $last      = self::LAST_COL;
        $headerRow = self::HEADER_ROW;
        $lastRow   = $headerRow + max($this->dataCount, 1);

        // Judul
        $sheet->mergeCells("A1:{$last}1");
        $sheet->mergeCells("A2:{$last}2");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header tabel
        $sheet->getStyle("A{$headerRow}:{$last}{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9EDF5']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        // Border + rata atas seluruh tabel
        $sheet->getStyle("A{$headerRow}:{$last}{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle("A" . ($headerRow + 1) . ":{$last}{$lastRow}")
            ->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);

        // Kolom pendek rata tengah: No, Tanggal, Shift, Line, 3 test piece, Jam
        foreach (['A', 'B', 'C', 'D', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'N', 'Q'] as $col) {
            $sheet->getStyle("{$col}" . ($headerRow + 1) . ":{$col}{$lastRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Batasi lebar kolom teks panjang (AutoSize bisa terlalu lebar)
        $sheet->getColumnDimension('O')->setAutoSize(false)->setWidth(40);
        $sheet->getColumnDimension('P')->setAutoSize(false)->setWidth(35);

        $sheet->freezePane('A' . ($headerRow + 1));
    }
}