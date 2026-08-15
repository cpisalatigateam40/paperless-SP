<?php

namespace App\Traits;

trait HasSortableReport
{
    /**
     * Terapkan sorting ke query report.
     *
     * $config:
     *  - report_date_column : nama kolom tanggal laporan di tabel utama (default 'date')
     *  - production_code    : ['relation' => 'details', 'column' => 'production_code'] (opsional)
     */
    protected function applyReportSort($query, $request, array $config = [])
    {
        $sortBy  = $request->get('sort_by', 'latest');
        $sortDir = $request->get('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';

        switch ($sortBy) {

            case 'production_code':
            if (!empty($config['production_code'])) {
                $pc = $config['production_code'];

                if (empty($pc['relation'])) {
                    // kolom langsung di tabel report (header)
                    $query->orderBy($pc['column'], $sortDir);
                } else {
                    // kolom di tabel detail (relasi hasMany)
                    $model = $query->getModel();
                    $relation = $model->{$pc['relation']}();

                    $relatedTable = $relation->getRelated()->getTable();
                    $foreignKey   = $relation->getForeignKeyName();
                    $localKey     = $relation->getLocalKeyName();
                    $mainTable    = $model->getTable();

                    $sub = \DB::table($relatedTable)
                        ->select($pc['column'])
                        ->whereColumn($relatedTable . '.' . $foreignKey, $mainTable . '.' . $localKey)
                        ->orderBy($pc['column'])
                        ->limit(1);

                    $query->orderByRaw('(' . $sub->toSql() . ') ' . $sortDir)
                        ->addBinding($sub->getBindings(), 'order');
                }
            }
            break;

            case 'report_date':
                $query->orderBy($config['report_date_column'] ?? 'date', $sortDir);
                break;

            case 'submitted_at':
                $query->orderBy('created_at', $sortDir);
                break;

            default:
                $query->latest();
                break;
        }

        return $query;
    }
}