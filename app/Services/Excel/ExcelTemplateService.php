<?php

namespace App\Services\Excel;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Facades\Excel;

class ExcelTemplateService
{
    public function generateWithDropdown(string $modelClass, string $filename)
    {
        $mapping      = $modelClass::getImportMapping();
        $masterFields = $modelClass::getMasterDataFields();
        // Shift: dropdown otomatis untuk semua modul yang punya kolom shift
        $shifts = config('paperless.shifts', []);
        if ($shifts && in_array('shift', $mapping, true) && !isset($masterFields['shift'])) {
            $masterFields['shift'] = ['label' => 'Shift', 'options' => $shifts];
        }

        // opsional: informasi nilai otomatis (mis. tanggal/shift dari session)
        $context = method_exists($modelClass, 'getImportContext')
            ? $modelClass::getImportContext()
            : [];

        // opsional: baris template yang sudah terisi (daftar item / master)
        $prefill = method_exists($modelClass, 'getImportPrefillRows')
            ? $modelClass::getImportPrefillRows()
            : [];

        $export = new class($mapping, $masterFields, $context, $prefill) implements WithMultipleSheets {
            public function __construct(
                private array $mapping,
                private array $masterFields,
                private array $context,
                private array $prefill
            ) {}

            public function sheets(): array
            {
                return [
                    new DataImportSheet($this->mapping, $this->masterFields, $this->prefill),
                    new MasterDataSheet($this->masterFields),
                    new InstructionSheet($this->mapping, $this->masterFields, $this->context),
                ];
            }
        };

        return Excel::download($export, $filename);
    }
}