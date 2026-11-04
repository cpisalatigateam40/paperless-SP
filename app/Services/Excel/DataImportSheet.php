<?php

namespace App\Services\Excel;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

/* Sheet 1: Data Import (header + dropdown) */
class DataImportSheet implements FromArray, WithHeadings, WithTitle, WithEvents
{
        // Kolom tanggal di template: tampil dd/mm/yyyy dan berformat Teks
    private const DATE_FIELDS = ['date', 'best_before', 'expired_date'];

    public function __construct(
        private array $mapping,
        private array $masterFields,
        private array $prefill = []
    ) {}

    public function title(): string
    {
        return 'Data Import';
    }

    // Tanda " *" hanya hiasan; saat import akan dibuang oleh ExcelImportService
    public function headings(): array
    {
        $headers = [];
        foreach ($this->mapping as $excelCol => $dbField) {
            $headers[] = $excelCol . ' *';
        }
        return $headers;
    }

    // Bila model punya getImportPrefillRows(): satu baris per entri ([key => nilai]),
    // diurutkan mengikuti mapping. Jika tidak: satu baris contoh.
    public function array(): array
    {
        if (!empty($this->prefill)) {
            return array_map(function (array $values) {
                $row = [];
                foreach ($this->mapping as $excelCol => $dbField) {
                    $value = $values[$dbField] ?? '';
                    $row[] = in_array($dbField, self::DATE_FIELDS, true) ? $this->toDmy($value) : $value;
                }
                return $row;
            }, $this->prefill);
        }

        $now = Carbon::now();
        $row = [];
        foreach ($this->mapping as $excelCol => $dbField) {
            $row[] = in_array($dbField, ['date', 'tanggal'], true) ? $now->format('d/m/Y') : '';
        }
        return [$row];
    }

    // "2026-10-27" -> "27/10/2026" (nilai lain dibiarkan)
    private function toDmy($value)
    {
        if (is_string($value) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
            return "{$m[3]}/{$m[2]}/{$m[1]}";
        }
        return $value;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $colCount  = count($this->mapping);

                if ($colCount === 0) {
                    return;
                }

                $lastColumn = Coordinate::stringFromColumnIndex($colCount);

                $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                for ($i = 1; $i <= $colCount; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(20);
                }
                $sheet->getRowDimension(1)->setRowHeight(25);

                $this->applyDropdowns($sheet);
                $this->applyDateFormat($sheet);
            },
        ];
    }

    private function applyDropdowns($sheet): void
    {
        $masterKeys  = array_keys($this->masterFields);
        $columnIndex = 1;

        foreach ($this->mapping as $excelCol => $dbField) {
            if (isset($this->masterFields[$dbField])) {
                $config       = $this->masterFields[$dbField];
                $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);

                // tiap field menunjuk kolomnya sendiri di sheet "Master Data"
                $pos          = array_search($dbField, $masterKeys, true) + 1;
                $masterLetter = Coordinate::stringFromColumnIndex($pos);

                $listSource = $config['list_source']
                    ?? sprintf("'Master Data'!\$%s\$2:\$%s\$1000", $masterLetter, $masterLetter);

                $validation = new DataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(false);
                $validation->setShowDropDown(true); // true = panah dropdown TAMPIL
                $validation->setFormula1($listSource);
                $validation->setShowErrorMessage(true);
                $validation->setErrorTitle('Nilai Tidak Valid');
                $validation->setError('Pilih nilai dari dropdown yang ada');

                $range = $columnLetter . '2:' . $columnLetter . '1000';
                $sheet->setDataValidation($range, $validation);
                $sheet->getStyle($range)->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF2CC']],
                ]);
            }
            $columnIndex++;
        }
    }

    private function applyDateFormat($sheet): void
    {
        $columnIndex = 1;
        foreach ($this->mapping as $excelCol => $dbField) {
            if (in_array($dbField, self::DATE_FIELDS, true)) {
                $letter = Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->getStyle("{$letter}2:{$letter}1000")
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            }
            $columnIndex++;
        }
    }
}