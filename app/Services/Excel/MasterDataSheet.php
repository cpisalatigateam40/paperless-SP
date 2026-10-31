<?php

namespace App\Services\Excel;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/* Sheet 2: Master Data (sumber dropdown) */
class MasterDataSheet implements FromArray, WithTitle, WithHeadings, WithEvents
{
    public function __construct(private array $masterFields) {}

    public function title(): string
    {
        return 'Master Data';
    }

    public function headings(): array
    {
        $headers = [];
        foreach ($this->masterFields as $fieldName => $config) {
            $headers[] = $config['label'] ?? $fieldName;
        }
        return $headers;
    }

    // Satu kolom per field; baris diisi sampai kolom terpanjang
    public function array(): array
    {
        $columns = [];
        $maxRows = 0;

        foreach ($this->masterFields as $fieldName => $config) {
            $items     = array_values($this->fetchMasterData($config));
            $columns[] = $items;
            $maxRows   = max($maxRows, count($items));
        }

        $data = [];
        for ($i = 0; $i < $maxRows; $i++) {
            $row = [];
            foreach ($columns as $column) {
                $row[] = $column[$i] ?? '';
            }
            $data[] = $row;
        }
        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $colCount = count($this->masterFields);

                if ($colCount === 0) {
                    return;
                }

                $lastColumn = Coordinate::stringFromColumnIndex($colCount);

                $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '70AD47']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                for ($i = 1; $i <= $colCount; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(25);
                }
                $sheet->getRowDimension(1)->setRowHeight(20);

                // kunci sheet agar pengguna tidak mengubah sumber dropdown
                $sheet->getProtection()->setSheet(true);
            },
        ];
    }

    // Sumber data: source (model) | options (array) | callback (closure)
    private function fetchMasterData(array $config): array
    {
        if (isset($config['source']) && is_string($config['source']) && class_exists($config['source'])) {
            $model    = $config['source'];
            $labelCol = $config['label_column'] ?? 'name';
            $valueCol = $config['value_column'] ?? 'id';

            $query = $model::query();
            foreach (($config['filter'] ?? []) as $key => $value) {
                if ($value !== null) {
                    $query->where($key, $value);
                }
            }

            return $query->get()->map(function ($item) use ($labelCol, $valueCol) {
                $label = $item->{$labelCol};
                $value = $item->{$valueCol};
                return $value === $label ? $label : $value . ' - ' . $label;
            })->all();
        }

        if (isset($config['options']) && is_array($config['options'])) {
            return $config['options'];
        }

        if (isset($config['callback']) && is_callable($config['callback'])) {
            return call_user_func($config['callback']);
        }

        return [];
    }
}