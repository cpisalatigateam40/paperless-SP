<?php

namespace App\Exports;

use App\Models\{RawMaterial, Premix};
use Maatwebsite\Excel\Concerns\{
    WithHeadings,
    FromArray,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class EmulsionMakingTemplateExport implements WithHeadings, FromArray, WithEvents
{
    public function headings(): array
    {
        return [
            'Tanggal',          // A
            'Shift',            // B
            'Time',             // C
            'QC',               // D
            'Group',            // E
            'Jenis Emulsi',     // F
            'Kode Prod',        // G
            'Bahan',            // H
            'Berat',            // I
            'Suhu',             // J
            'Kesesuaian formula',       // K
            'Awal Proses',      // L
            'Akhir Proses',     // M
            'Warna Emulsi',     // N
            'Tekstur Emulsi',   // O
            'Suhu Emulsi',      // P
            'Hasil Emulsi',     // Q
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
            'Emulsi Oil',
            'EM0126AA01',
            '',
            '10',
            '5',
            '✓',
            '08:00',
            '08:30',
            '✓',
            '✓',
            '7',
            'OK',
        ]];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $spreadsheet = $event->sheet->getDelegate()->getParent();
                $sheet = $event->sheet->getDelegate();

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
                $masterBahan->setSheetState(
                    \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN
                );

                /* ================= MASTER EMULSI ================= */
                $masterEmulsi = $spreadsheet->createSheet();
                $masterEmulsi->setTitle('MASTER_EMULSI');

                $emulsis = [
                    'Emulsi Oil',
                    'Emulsi Skin',
                    'Emulsi Gel',
                    'Emulsi GMB',
                ];

                $row = 1;
                foreach ($emulsis as $e) {
                    $masterEmulsi->setCellValue("A{$row}", $e);
                    $row++;
                }

                $lastEmulsiRow = $row - 1;
                $masterEmulsi->setSheetState(
                    \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN
                );

                /* ================= DROPDOWN ================= */
                $bahanDropdown = new DataValidation();
                $bahanDropdown->setType(DataValidation::TYPE_LIST);
                $bahanDropdown->setAllowBlank(true);
                $bahanDropdown->setShowDropDown(true);
                $bahanDropdown->setFormula1("=MASTER_BAHAN!A1:A{$lastBahanRow}");

                $checkDropdown = new DataValidation();
                $checkDropdown->setType(DataValidation::TYPE_LIST);
                $checkDropdown->setAllowBlank(true);
                $checkDropdown->setShowDropDown(true);
                $checkDropdown->setFormula1('"✓,x"');

                // Jenis Emulsi
                $emulsiDropdown = new DataValidation();
                $emulsiDropdown->setType(DataValidation::TYPE_LIST);
                $emulsiDropdown->setAllowBlank(false);
                $emulsiDropdown->setShowDropDown(true);
                $emulsiDropdown->setFormula1("=MASTER_EMULSI!A1:A{$lastEmulsiRow}");

                // Check (✓ / x)
                $checkDropdown = new DataValidation();
                $checkDropdown->setType(DataValidation::TYPE_LIST);
                $checkDropdown->setAllowBlank(true);
                $checkDropdown->setShowDropDown(true);
                $checkDropdown->setFormula1('"✓,x"');

                // Hasil Emulsi
                $resultDropdown = new DataValidation();
                $resultDropdown->setType(DataValidation::TYPE_LIST);
                $resultDropdown->setAllowBlank(true);
                $resultDropdown->setShowDropDown(true);
                $resultDropdown->setFormula1('"OK,Tidak OK"');

                for ($r = 2; $r <= 1000; $r++) {
                    $sheet->getCell("H{$r}")->setDataValidation(clone $bahanDropdown);
                    $sheet->getCell("K{$r}")->setDataValidation(clone $checkDropdown);
                    // Jenis Emulsi (F)
                    $sheet->getCell("F{$r}")
                        ->setDataValidation(clone $emulsiDropdown);

                    // Warna Emulsi (N)
                    $sheet->getCell("N{$r}")
                        ->setDataValidation(clone $checkDropdown);

                    // Tekstur Emulsi (O)
                    $sheet->getCell("O{$r}")
                        ->setDataValidation(clone $checkDropdown);

                    // Hasil Emulsi (Q)
                    $sheet->getCell("Q{$r}")
                        ->setDataValidation(clone $resultDropdown);
                }
            }
        ];
    }
}
