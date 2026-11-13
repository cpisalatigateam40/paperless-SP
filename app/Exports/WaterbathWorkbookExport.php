<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WaterbathWorkbookExport implements WithMultipleSheets
{
    public function __construct(
        private $legacy,
        private $master,
        private $cikande,
        private string $periodLabel,
    ) {}

    public function sheets(): array
    {
        $sheets = [];

        // Sheet lama tampil kalau ada datanya, atau kalau tidak ada data sama sekali (supaya file tidak kosong)
        if ($this->legacy->isNotEmpty()
            || ($this->master->isEmpty() && $this->cikande->isEmpty())) {
            $sheets[] = new WaterbathExport($this->legacy, $this->periodLabel);
        }

        if ($this->master->isNotEmpty()) {
            $sheets[] = new WaterbathMasterExport($this->master, $this->periodLabel);
        }

        if ($this->cikande->isNotEmpty()) {
            $sheets[] = new WaterbathCikandeExport($this->cikande, $this->periodLabel);
        }

        return $sheets;
    }
}