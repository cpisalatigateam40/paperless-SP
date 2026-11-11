<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WaterbathWorkbookExport implements WithMultipleSheets
{
    public function __construct(
        private $legacy,
        private $master,
        private string $periodLabel,
    ) {}

    public function sheets(): array
    {
        $sheets = [];

        // sheet lama tampil kalau ada datanya (atau kalau sama sekali tidak ada data, supaya file tidak kosong)
        if ($this->legacy->isNotEmpty() || $this->master->isEmpty()) {
            $sheets[] = new WaterbathExport($this->legacy, $this->periodLabel);
        }

        if ($this->master->isNotEmpty()) {
            $sheets[] = new WaterbathMasterExport($this->master, $this->periodLabel);
        }

        return $sheets;
    }
}