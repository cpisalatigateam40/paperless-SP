<?php

namespace App\Exports;

use App\Models\MasterWaterbathParameter;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class WaterbathMasterExport implements WithEvents, WithTitle
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function title(): string { return 'Waterbath (Master Data)'; }

    /**
     * Definisi kolom: group (null = kolom tunggal), label, getter, cek-merah, numeric.
     * Getter menerima konteks: ['no', 'report', 'run', 'shiftNum', 'shiftGroup'].
     */
    private function columns(): array
    {
        $cols = [];
        $add = function (?string $group, string $label, callable $get, ?callable $ok = null, bool $numeric = false) use (&$cols) {
            $cols[] = compact('group', 'label', 'get', 'ok', 'numeric');
        };

        // ── Informasi umum ─────────────────────────────────────────
        $add(null, 'No',      fn ($c) => $c['no']);
        $add(null, 'Tanggal', fn ($c) => Carbon::parse($c['report']->date)->format('d/m/Y'));
        $add(null, 'Shift',   fn ($c) => $c['shiftNum'] ?: ($c['report']->shift ?? '-'));
        $add(null, 'QC',      fn ($c) => $c['report']->created_by ?? '-');
        $add(null, 'Group',   fn ($c) => $c['shiftGroup'] ?: '-');
        $add(null, 'Nama Produk',  fn ($c) => $c['report']->product->product_name ?? '-');
        $add(null, "Gramasi\n(gr)", fn ($c) => $c['report']->gramase, null, true);
        $add(null, 'Kode Produk',  fn ($c) => $c['report']->batches->first()?->batch_code ?? '-');
        $add(null, 'Proses', function ($c) {
            $n = $c['run']?->run_number;
            if ($n === null) return '-';
            return $n === 1 ? 'Verifikasi' : 'Pasteurisasi Ulang' . ($n > 2 ? ' #' . ($n - 1) : '');
        });
        $add(null, "No.\nWaterbath", fn ($c) => $c['run']?->waterbath_number ?? '-');
        $add(null, "Initial Temp\n(°C)", fn ($c) => $c['run']?->initial_temp,
            fn ($c) => $c['run']?->isInitialTempOk(), true);
        $add(null, "Std Initial Temp\n(°C)", fn ($c) => $c['run']?->initial_temp_range ?? '-');

        // ── Parameter: Pasteurization / Cooling / Drying ──────────
        foreach (MasterWaterbathParameter::PARAMETERS as $key => $label) {
            $p = fn ($c) => $c['run']?->parameter($key);
            $g = $label;

            if ($key === MasterWaterbathParameter::DRYING) {
                $add($g, "Set Temp\nHot Zone (°C)",  fn ($c) => $p($c)?->set_temp_range ?? '-');
                $add($g, "Set Temp\nCold Zone (°C)", fn ($c) => $p($c)?->cold_zone_set_range ?? '-');
                $add($g, "Act Temp\nHot Zone (°C)",  fn ($c) => $p($c)?->act_temp,
                    fn ($c) => $p($c)?->isActTempOk(), true);
                $add($g, "Act Temp\nCold Zone (°C)", fn ($c) => $p($c)?->cold_zone_act,
                    fn ($c) => $p($c)?->isColdZoneActOk(), true);
            } else {
                $add($g, "Set Temp\n(°C)", fn ($c) => $p($c)?->set_temp_range ?? '-');
                $add($g, "Act Temp\n(°C)", fn ($c) => $p($c)?->act_temp,
                    fn ($c) => $p($c)?->isActTempOk(), true);
            }

            $add($g, "Set Time\n(menit)", fn ($c) => $p($c)?->set_time, null, true);
            $add($g, "Act Time\n(menit)", fn ($c) => $p($c)?->act_time,
                fn ($c) => $p($c)?->isActTimeOk(), true);
            $add($g, "Std CT Prod.\n(°C)", fn ($c) => $p($c)?->std_ct_range ?? '-');
            $add($g, "Act CT Prod.\n(°C)", fn ($c) => $p($c)?->act_ct,
                fn ($c) => $p($c)?->isActCtOk(), true);
        }

        // ── Penutup ────────────────────────────────────────────────
        $add(null, 'Sensori', function ($c) {
            $s = $c['run']?->sensor_ok;
            return $s === null ? '-' : ($s ? 'Ok' : 'Tidak Ok');
        });
        $add(null, 'Catatan', fn ($c) => filled($c['run']?->notes) ? $c['run']->notes : '-');

        return $cols;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $cols    = $this->columns();
                $total   = count($cols);
                $lastCol = Coordinate::stringFromColumnIndex($total);

                // ── Judul ──────────────────────────────────────────
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Verifikasi Proses Pasteurisasi Produk di Waterbath');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Periode: ' . $this->periodLabel);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // ── Header (row 4 = group, row 5 = sub) ────────────
                $i = 1;
                while ($i <= $total) {
                    $col    = $cols[$i - 1];
                    $letter = Coordinate::stringFromColumnIndex($i);

                    if ($col['group'] === null) {
                        $sheet->mergeCells("{$letter}4:{$letter}5");
                        $sheet->setCellValue("{$letter}4", $col['label']);
                        $i++;
                        continue;
                    }

                    $end = $i; // kolom terakhir dalam grup yang sama
                    while ($end < $total && $cols[$end]['group'] === $col['group']) {
                        $end++;
                    }

                    $endLetter = Coordinate::stringFromColumnIndex($end);
                    $sheet->mergeCells("{$letter}4:{$endLetter}4");
                    $sheet->setCellValue("{$letter}4", $col['group']);

                    for ($k = $i; $k <= $end; $k++) {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($k) . '5', $cols[$k - 1]['label']);
                    }

                    $i = $end + 1;
                }

                $sheet->getStyle("A4:{$lastCol}5")->getFont()->setBold(true);
                $sheet->getStyle("A4:{$lastCol}5")->getAlignment()
                    ->setHorizontal('center')->setVertical('center')->setWrapText(true);
                $sheet->getStyle("A4:{$lastCol}5")->getBorders()
                    ->getAllBorders()->setBorderStyle('thin');

                // ── Data (mulai row 6, satu baris per proses) ──────
                $dataRow = 6;
                $no      = 1;

                foreach ($this->reports as $report) {
                    [$shiftNum, $shiftGroup] = array_pad(explode('-', $report->shift ?? '', 2), 2, '');

                    $runs = $report->runs->isNotEmpty() ? $report->runs : collect([null]);

                    foreach ($runs as $run) {
                        $ctx = compact('report', 'run', 'shiftNum', 'shiftGroup') + ['no' => $no];

                        foreach ($cols as $idx => $col) {
                            $coord = Coordinate::stringFromColumnIndex($idx + 1) . $dataRow;
                            $value = ($col['get'])($ctx);

                            if ($col['numeric'] && is_numeric($value)) {
                                $value = (float) $value;
                            } elseif ($value === null || $value === '') {
                                $value = '-';
                            }

                            $sheet->setCellValue($coord, $value);

                            // Merah kalau di luar standar master
                            if ($col['ok'] && ($col['ok'])($ctx) === false) {
                                $font = $sheet->getStyle($coord)->getFont();
                                $font->setBold(true);
                                $font->getColor()->setARGB('FFD00000');
                            }
                        }

                        $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")
                            ->getAlignment()->setHorizontal('center')->setVertical('center');
                        $sheet->getStyle("A{$dataRow}:{$lastCol}{$dataRow}")
                            ->getBorders()->getAllBorders()->setBorderStyle('thin');

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

                // ── Format angka 2 desimal + lebar kolom ───────────
                foreach ($cols as $idx => $col) {
                    $letter = Coordinate::stringFromColumnIndex($idx + 1);

                    if ($col['numeric'] && $dataRow > 6) {
                        $sheet->getStyle("{$letter}6:{$letter}" . ($dataRow - 1))
                            ->getNumberFormat()->setFormatCode('0.00');
                    }

                    if ($col['label'] === 'Catatan') {
                        $sheet->getColumnDimension($letter)->setWidth(40);
                        if ($dataRow > 6) {
                            $sheet->getStyle("{$letter}6:{$letter}" . ($dataRow - 1))
                                ->getAlignment()->setWrapText(true)->setHorizontal('left');
                        }
                    } else {
                        $sheet->getColumnDimension($letter)->setAutoSize(true);
                    }
                }

                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(34);
            },
        ];
    }
}