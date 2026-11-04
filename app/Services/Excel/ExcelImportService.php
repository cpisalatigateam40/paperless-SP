<?php

namespace App\Services\Excel;

use App\Services\Excel\Contracts\CustomImportRow;
use App\Services\Excel\Contracts\ImportableModel;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportService
{
    public function import($file, string $modelClass, array $additionalData = []): array
    {
        if (!is_subclass_of($modelClass, ImportableModel::class)) {
            throw new Exception("{$modelClass} harus implement ImportableModel.");
        }

        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        DB::beginTransaction();

        try {
            $sheets = Excel::toArray(new \stdClass(), $file);
            $data   = $sheets[0] ?? [];

            if (empty($data)) {
                throw new Exception('File Excel kosong atau tidak valid.');
            }

            // baris pertama = header; buang tanda " *"
            $headers = array_shift($data);
            $headers = array_map(fn ($h) => trim(str_replace('*', '', (string) $h)), $headers);

            $mapping   = $modelClass::getImportMapping();
            $rules     = $modelClass::getImportRules();
            $customRow = is_subclass_of($modelClass, CustomImportRow::class);
            $fillable  = $customRow ? [] : array_flip((new $modelClass)->getFillable());

            foreach ($data as $rowIndex => $row) {
                // lewati baris yang seluruh selnya kosong
                if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) {
                    continue;
                }

                $excelRow = $rowIndex + 2; // +1 header, +1 karena index mulai 0

                try {
                    $rowData = $this->mapRowToFields($row, $headers, $mapping);
                    $rowData = array_merge($rowData, $additionalData);
                    $rowData = $modelClass::transformImportRow($rowData);

                    $validator = Validator::make($rowData, $rules);
                    if ($validator->fails()) {
                        $results['failed']++;
                        $results['errors'][] = "Baris {$excelRow}: " . $validator->errors()->first();
                        continue;
                    }

                    // transaksi di dalam transaksi = savepoint
                    // (1 baris gagal tidak membatalkan baris lainnya)
                    DB::transaction(function () use ($modelClass, $rowData, $customRow, $fillable) {
                        if ($customRow) {
                            $modelClass::importRow($rowData);
                        } else {
                            $modelClass::create(array_intersect_key($rowData, $fillable));
                        }
                    });

                    $results['success']++;
                } catch (\Throwable $e) {
                    $results['failed']++;
                    $results['errors'][] = "Baris {$excelRow}: " . $e->getMessage();
                }
            }

            $results['success'] > 0 ? DB::commit() : DB::rollBack();

            return $results;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function mapRowToFields(array $row, array $headers, array $mapping): array
    {
        $result = [];

        foreach ($mapping as $excelCol => $dbField) {
            $index = array_search($excelCol, $headers, true);

            if ($index !== false && isset($row[$index])) {
                $value = is_string($row[$index]) ? trim($row[$index]) : $row[$index];

                if ($value !== null && $value !== '') {
                    $result[$dbField] = $value;
                }
            }
        }

        return $result;
    }
}