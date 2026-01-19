<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class PremixTemplateExport implements FromArray, WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'name',
            'producer',
            'shelf_life',
            'unit'
        ];
    }

    public function array(): array
    {
        return [
            ['Premix Contoh', 'PT Contoh', '12', 'Bulan'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()
                ->getStyle('C2:C1000')
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_TEXT);
                
                $validation = new DataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1('"Jam,Hari,Bulan,Tahun"');

                // Apply dropdown ke kolom D baris 2–1000
                for ($row = 2; $row <= 1000; $row++) {
                    $event->sheet
                        ->getDelegate()
                        ->getCell("D{$row}")
                        ->setDataValidation(clone $validation);
                }
            },
        ];
    }
}
