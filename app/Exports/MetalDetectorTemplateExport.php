<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\{
    WithHeadings,
    FromArray,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class MetalDetectorTemplateExport implements WithHeadings, FromArray, WithEvents
{
    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Shift',
            'Time',
            'QC',
            'Group',
            'Nama produk',
            'Kode prod',
            "Speci. Fe 1,5 mm",
            "Speci. Non-Fe 2,0 mm",
            "Speci. SUS 2,5 mm",
            'Hasil verifikasi',
            'Ketidaksesuaian',
            'Tindakan koreksi',
            'Hasil verifikasi setelah tindakan perbaikan',
        ];
    }

    public function array(): array
    {
        return [[
            1,
            '02/01/2026',
            '1',
            '08:00',
            'QC Andi',
            'D',
            '',
            'MD0126AA01',
            '✓',
            '✓',
            '✓',
            '✓',
            '',
            '',
            '✓',
        ]];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $spreadsheet = $event->sheet->getDelegate()->getParent();
                $sheet = $event->sheet->getDelegate();

                /* ================= MASTER PRODUCT ================= */
                $masterProduct = $spreadsheet->createSheet();
                $masterProduct->setTitle('MASTER_PRODUCT');

                $row = 1;
                foreach (Product::orderBy('product_name')->get() as $product) {
                    $masterProduct->setCellValue("A{$row}", $product->product_name);
                    $row++;
                }

                $lastProductRow = $row - 1;
                $masterProduct->setSheetState(
                    \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN
                );

                /* ================= DROPDOWNS ================= */

                // Produk
                $productDropdown = new DataValidation();
                $productDropdown->setType(DataValidation::TYPE_LIST);
                $productDropdown->setAllowBlank(false);
                $productDropdown->setShowDropDown(true);
                $productDropdown->setFormula1("=MASTER_PRODUCT!A1:A{$lastProductRow}");

                // ✓ / x
                $checkDropdown = new DataValidation();
                $checkDropdown->setType(DataValidation::TYPE_LIST);
                $checkDropdown->setAllowBlank(true);
                $checkDropdown->setShowDropDown(true);
                $checkDropdown->setFormula1('"✓,x"');

                // OK / Tidak OK
                $resultDropdown = new DataValidation();
                $resultDropdown->setType(DataValidation::TYPE_LIST);
                $resultDropdown->setAllowBlank(true);
                $resultDropdown->setShowDropDown(true);
                $resultDropdown->setFormula1('"✓,x"');

                for ($r = 2; $r <= 1000; $r++) {

                    // Nama produk (G)
                    $sheet->getCell("G{$r}")
                        ->setDataValidation(clone $productDropdown);

                    // Speci FE / Non FE / SUS
                    foreach (['I', 'J', 'K'] as $col) {
                        $sheet->getCell("{$col}{$r}")
                            ->setDataValidation(clone $checkDropdown);
                    }

                    // Hasil verifikasi
                    $sheet->getCell("L{$r}")
                        ->setDataValidation(clone $resultDropdown);

                    // Hasil verifikasi setelah tindakan
                    $sheet->getCell("O{$r}")
                        ->setDataValidation(clone $resultDropdown);
                }
            }
        ];
    }
}
