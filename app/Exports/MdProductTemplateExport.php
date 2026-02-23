<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MdProductTemplateExport implements FromArray, WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'No','Tanggal','Shift','Time','QC','Group',
            'Nama produk','Gramase',
            'Kode Produksi','Best Before',
            'No Program','Tipe / Mesin',

            'Fe 1.5 mm (D)','Fe 1.5 mm (T)','Fe 1.5 mm (B)','Fe 1.5 mm (DL)',
            'Non Fe 2 mm (D)','Non Fe 2 mm (T)','Non Fe 2 mm (B)','Non Fe 2 mm (DL)',
            'SUS 2.5 mm (D)','SUS 2.5 mm (T)','SUS 2.5 mm (B)','SUS 2.5 mm (DL)',

            'Tindakan Perbaikan',
            'Verifikasi setelah perbaikan',
        ];
    }

    public function array(): array
    {
        return [[
            1,
            now()->format('d/m/Y'),
            '2',
            '08:02',
            auth()->user()->name,
            'A',
            '',
            '500 g',
            'MD0126AA03',
            '20/02/2027',
            '',
            '',
            'OK','OK','OK','OK',
            'OK','OK','OK','OK',
            'OK','OK','OK','OK',
            '',
            'OK'
        ]];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $spreadsheet = $event->sheet->getDelegate()->getParent();
                $sheet = $event->sheet->getDelegate();
                $maxRow = 1000;

                /* ================= MASTER ================= */
                $master = $spreadsheet->createSheet();
                $master->setTitle('MASTER_MD');
                $master->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                // Produk
                $r = 1;
                foreach (Product::orderBy('product_name')->get() as $p) {
                    $master->setCellValue("A{$r}", $p->product_name.' - '.$p->nett_weight.' g');
                    $r++;
                }
                $lastProductRow = $r - 1;

                // Mesin / Tipe
                $machines = ['Manual','CFS','Colimatic','Multivac'];
                foreach ($machines as $i => $m) {
                    $master->setCellValue('B'.($i+1), $m);
                }

                // OK / Tidak OK
                $master->setCellValue('C1', 'OK');
                $master->setCellValue('C2', 'Tidak OK');

                /* ================= VALIDATION OBJECT ================= */

                // Produk
                $productValidation = new DataValidation();
                $productValidation->setType(DataValidation::TYPE_LIST);
                $productValidation->setAllowBlank(true);
                $productValidation->setShowDropDown(true);
                $productValidation->setFormula1("=MASTER_MD!A1:A{$lastProductRow}");

                // Mesin
                $machineValidation = new DataValidation();
                $machineValidation->setType(DataValidation::TYPE_LIST);
                $machineValidation->setAllowBlank(true);
                $machineValidation->setShowDropDown(true);
                $machineValidation->setFormula1("=MASTER_MD!B1:B4");

                // OK / Tidak OK
                $okValidation = new DataValidation();
                $okValidation->setType(DataValidation::TYPE_LIST);
                $okValidation->setAllowBlank(true);
                $okValidation->setShowDropDown(true);
                $okValidation->setFormula1("=MASTER_MD!C1:C2");

                /* ================= APPLY ================= */
                for ($row = 2; $row <= $maxRow; $row++) {

                    // Nama Produk (G)
                    $sheet->getCell("G{$row}")
                        ->setDataValidation(clone $productValidation);

                    // Tipe / Mesin (L)
                    $sheet->getCell("L{$row}")
                        ->setDataValidation(clone $machineValidation);

                    // Specimen M–X
                    foreach (range('M','X') as $col) {
                        $sheet->getCell("{$col}{$row}")
                            ->setDataValidation(clone $okValidation);
                    }

                    // Verifikasi (Z)
                    $sheet->getCell("Z{$row}")
                        ->setDataValidation(clone $okValidation);
                }

                $sheet->freezePane('A2');
                $sheet->getStyle('A1:Z1')->getFont()->setBold(true);
            }
        ];
    }
}