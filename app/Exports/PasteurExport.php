<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PasteurExport implements WithMultipleSheets
{
    public function __construct(
        private $reports,
        private string $periodLabel,
    ) {}

    public function sheets(): array
    {
        $sheets = [new PasteurRunSheet($this->reports, $this->periodLabel)];

        $hasLegacy = $this->reports->contains(
            fn ($r) => $r->details->contains(fn ($d) => $d->isLegacy())
        );

        if ($hasLegacy) {
            $sheets[] = new PasteurLegacySheet($this->reports, $this->periodLabel);
        }

        return $sheets;
    }
}