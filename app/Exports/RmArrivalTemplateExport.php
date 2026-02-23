<?php

namespace App\Exports;

use App\Models\{RawMaterial, Premix, Section};
use Maatwebsite\Excel\Concerns\{
    WithHeadings,
    FromArray,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class RmArrivalTemplateExport implements WithHeadings, FromArray, WithEvents
{
    public function headings(): array
    {
        return [
            'Tanggal',          // A
            'Shift',            // B
            'Time',             // C
            'QC',               // D
            'Group',            // E
            'Section',          // F
            'Bahan',            // G
            'Kode prod.',       // H
            'Kondisi',          // I
            'Supplier',         // J
            'Kemasan',          // K
            'Kenampakan',       // L
            'Aroma',            // M
            'Warna',            // N
            'Kontaminasi',      // O
            'Suhu (°C)',        // P
            'Problem',          // Q
            'Tindakan koreksi', // R
        ];
    }

    public function array(): array
    {
        return [[
            '02/01/2026',
            '1',
            '08:00',
            'QC Andi',
            'D',
            '',
            '',
            'QA01301AA0',
            'Fresh (F)',
            '',
            '✓',
            '✓',
            '✓',
            '✓',
            'x',
            '5',
            '',
            '',
        ]];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $spreadsheet = $event->sheet->getDelegate()->getParent();
                $sheet = $event->sheet->getDelegate();

                /* ================= MASTER SECTION ================= */
                $masterSection = $spreadsheet->createSheet();
                $masterSection->setTitle('MASTER_SECTION');

                $row = 1;
                $sections = Section::where('area_uuid', auth()->user()->area_uuid)
                    ->whereIn('section_name', ['Seasoning', 'Chillroom'])
                    ->orderBy('section_name')
                    ->get();

                foreach ($sections as $section) {
                    $masterSection->setCellValue("A{$row}", $section->section_name);
                    $row++;
                }

                $lastSectionRow = $row - 1;
                $masterSection->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

                /* ================= MASTER BAHAN ================= */
                $masterBahan = $spreadsheet->createSheet();
                $masterBahan->setTitle('MASTER_BAHAN');

                $row = 1;
                foreach (RawMaterial::orderBy('material_name')->get() as $rm) {
                    $masterBahan->setCellValue("A{$row}", $rm->material_name);
                    $row++;
                }
                foreach (Premix::orderBy('name')->get() as $pm) {
                    $masterBahan->setCellValue("A{$row}", $pm->name);
                    $row++;
                }

                $lastBahanRow = $row - 1;
                $masterBahan->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

                /* ================= MASTER SUPPLIER ================= */
                $masterSupplier = $spreadsheet->createSheet();
                $masterSupplier->setTitle('MASTER_SUPPLIER');

                $row = 1;
                foreach ($this->suppliersByArea() as $supplier) {
                    $masterSupplier->setCellValue("A{$row}", $supplier);
                    $row++;
                }

                $lastSupplierRow = $row - 1;
                $masterSupplier->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

                /* ================= DROPDOWNS ================= */

                $sectionDropdown = new DataValidation();
                $sectionDropdown->setType(DataValidation::TYPE_LIST);
                $sectionDropdown->setAllowBlank(false);
                $sectionDropdown->setShowDropDown(true);
                $sectionDropdown->setFormula1("=MASTER_SECTION!A1:A{$lastSectionRow}");

                $bahanDropdown = new DataValidation();
                $bahanDropdown->setType(DataValidation::TYPE_LIST);
                $bahanDropdown->setAllowBlank(true);
                $bahanDropdown->setShowDropDown(true);
                $bahanDropdown->setFormula1("=MASTER_BAHAN!A1:A{$lastBahanRow}");

                $supplierDropdown = new DataValidation();
                $supplierDropdown->setType(DataValidation::TYPE_LIST);
                $supplierDropdown->setAllowBlank(true);
                $supplierDropdown->setShowDropDown(true);
                $supplierDropdown->setFormula1("=MASTER_SUPPLIER!A1:A{$lastSupplierRow}");

                $checkDropdown = new DataValidation();
                $checkDropdown->setType(DataValidation::TYPE_LIST);
                $checkDropdown->setAllowBlank(true);
                $checkDropdown->setShowDropDown(true);
                $checkDropdown->setFormula1('"✓,x"');

                $kondisiDropdown = new DataValidation();
                $kondisiDropdown->setType(DataValidation::TYPE_LIST);
                $kondisiDropdown->setAllowBlank(true);
                $kondisiDropdown->setShowDropDown(true);
                $kondisiDropdown->setFormula1('"Fresh (F),Thawing (Th),Frozen (Fr)"');

                for ($r = 2; $r <= 1000; $r++) {

                    $sheet->getCell("F{$r}")->setDataValidation(clone $sectionDropdown);
                    $sheet->getCell("G{$r}")->setDataValidation(clone $bahanDropdown);
                    $sheet->getCell("I{$r}")->setDataValidation(clone $kondisiDropdown);
                    $sheet->getCell("J{$r}")->setDataValidation(clone $supplierDropdown);

                    foreach (['K', 'L', 'M', 'N', 'O'] as $col) {
                        $sheet->getCell("{$col}{$r}")
                            ->setDataValidation(clone $checkDropdown);
                    }
                }
            }
        ];
    }

    private function suppliersByArea(): array
    {
        $areaName = auth()->user()->area->name ?? null;

        return [
            'Bandung' => ['Bandung', 'Majalengka', 'Salatiga', 'Cikande', 'Banyumas'],
            'Cikande 1' => [
                'Cikande 1', 'Cikande 3', 'Bandung', 'Banyumas', 'Pemalang',
                'Sragen', 'Madiun', 'Majalengka', 'Mojokerto', 'Salatiga', 'Bondowoso',
            ],
            'Medan' => [
                'Cikande 1', 'Cikande 3', 'Bandung', 'Banyumas', 'Pemalang',
                'Sragen', 'Madiun', 'Majalengka', 'Ngoro', 'Bondowoso', 'Salatiga', 'Medan',
            ],
            'Ngoro - Mojokerto' => ['Ngoro', 'Madiun', 'Bondowoso', 'Majalengka'],
            'Salatiga' => ['Salatiga', 'Pemalang', 'Sragen', 'Madiun', 'Banyumas'],
        ][$areaName] ?? [];
    }
}