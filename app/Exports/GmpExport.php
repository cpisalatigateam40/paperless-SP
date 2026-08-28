<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GmpExport implements WithMultipleSheets
{
    public function __construct(
        private $headers,
        private string $periodLabel,
    ) {}

    public function sheets(): array
    {
        return [
            new GmpKaryawanSheetExport($this->headers, $this->periodLabel),
            new GmpSanitasiSheetExport($this->headers, $this->periodLabel),
        ];
    }
}