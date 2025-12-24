<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Premix;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FormulaTemplateExport implements FromArray, WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'formula_name',
            'product_name',
            'formulation_name',
            'raw_material',
            'raw_weight',
            'premix',
            'premix_weight',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Formula A',
                'Nugget Ayam',
                'Adonan Utama',
                'Daging Ayam',
                50,
                'Premix A',
                5,
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $areaUuid = Auth::user()->area_uuid;
                $sheet = $event->sheet->getDelegate();
                $spreadsheet = $sheet->getParent();

                // Sheet MASTER_DATA
                $master = $spreadsheet->createSheet();
                $master->setTitle('MASTER_DATA');
                $master->setCellValue('A1', 'products');
                $master->setCellValue('B1', 'raw_materials');
                $master->setCellValue('C1', 'premixes');

                $row = 2;

                foreach (
                    Product::where('area_uuid', $areaUuid)
                    ->orderBy('product_name')
                    ->selectRaw('MIN(uuid) as uuid, product_name')
                    ->groupBy('product_name')
                    ->get() as $product
                ) {
                    $master->setCellValue("A{$row}", $product->product_name);
                    $row++;
                }

                $row = 2;
                foreach (
                    RawMaterial::where('area_uuid', $areaUuid)->orderBy('material_name')->get() as $raw
                ) {
                    $master->setCellValue("B{$row}", $raw->material_name);
                    $row++;
                }

                $row = 2;
                foreach (
                    Premix::where('area_uuid', $areaUuid)->orderBy('name')->get() as $premix
                ) {
                    $master->setCellValue("C{$row}", $premix->name);
                    $row++;
                }

                // Set dropdown
                $this->setDropdown($sheet, 'B', '=MASTER_DATA!$A$2:$A$500');
                $this->setDropdown($sheet, 'D', '=MASTER_DATA!$B$2:$B$500');
                $this->setDropdown($sheet, 'F', '=MASTER_DATA!$C$2:$C$500');

                $master->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
            }
        ];
    }

    private function setDropdown($sheet, string $column, string $range)
    {
        for ($row = 2; $row <= 500; $row++) {
            $validation = $sheet->getCell("{$column}{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1($range);
        }
    }
}
