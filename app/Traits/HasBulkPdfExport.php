<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use setasign\Fpdi\Fpdi;

trait HasBulkPdfExport
{
    abstract protected function getBulkExportModelClass(): string;
    abstract protected function getBulkExportView(): string;

    protected function getBulkExportEagerLoad(): array
    {
        return [];
    }

    protected function getBulkExportExtraData($report): array
    {
        return [];
    }

    protected function getBulkExportDateColumn(): string
    {
        return 'date';
    }

    protected function getBulkExportPaper(): array
    {
        return ['A4', 'landscape'];
    }

    protected function getBulkExportFileName(): string
    {
        return 'laporan';
    }

    protected function getBulkExportShiftColumn(): string
    {
        return 'shift';
    }

    protected function getBulkExportShiftOptions(): array
    {
        // key = value yang dikirim form & dicocokkan ke DB (prefix sebelum "-"), value = label tampilan
        return [
            '1' => 'Shift 1',
            '2' => 'Shift 2',
            '3' => 'Shift 3',
        ];
    }

    public function exportPdfBulk(Request $request)
    {
        $request->validate([
            'export_type' => 'required|in:range,month,shift',
            'start_date'  => 'nullable|required_if:export_type,range,shift|date',
            'end_date'    => 'nullable|required_if:export_type,range,shift|date|after_or_equal:start_date',
            'month'       => 'nullable|required_if:export_type,month|integer|between:1,12',
            'year'        => 'nullable|required_if:export_type,month|integer|digits:4',
            'shift'       => ['nullable', 'required_if:export_type,shift', \Illuminate\Validation\Rule::in(array_merge(['semua'], array_keys($this->getBulkExportShiftOptions())))],
        ]);

        $modelClass = $this->getBulkExportModelClass();
        $dateColumn = $this->getBulkExportDateColumn();

        $query = $modelClass::with($this->getBulkExportEagerLoad());

        if ($request->export_type === 'month') {
            $start = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth();
            $end   = (clone $start)->endOfMonth();
            $labelPeriod = $start->format('Ym');
        } else {
            // 'range' dan 'shift' sama-sama pakai start_date/end_date
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end   = Carbon::parse($request->end_date)->endOfDay();
            $labelPeriod = $start->format('Ymd') . '-' . $end->format('Ymd');

            if ($request->export_type === 'shift' && $request->shift !== 'semua') {
                $query->where($this->getBulkExportShiftColumn(), 'LIKE', $request->shift . '-%');
                $labelPeriod .= '_shift' . $request->shift;
            }
        }

        $reports = $query->whereBetween($dateColumn, [$start, $end])
            ->orderBy($dateColumn)
            ->get();

        if ($reports->isEmpty()) {
            return back()->with('error', 'Tidak ada laporan pada periode yang dipilih.');
        }

        [$format, $orientation] = $this->getBulkExportPaper();

        $tmpFiles = [];

        try {
            $fpdi = new Fpdi($orientation === 'landscape' ? 'L' : 'P', 'mm', $format);

            foreach ($reports as $report) {
                $viewData = array_merge(
                    ['report' => $report],
                    $this->getBulkExportExtraData($report)
                );

                $pdfContent = PDF::loadView($this->getBulkExportView(), $viewData)
                    ->setPaper($format, $orientation)
                    ->output();

                $tmpFile = tempnam(sys_get_temp_dir(), 'bulk_pdf_') . '.pdf';
                file_put_contents($tmpFile, $pdfContent);
                $tmpFiles[] = $tmpFile;

                $pageCount = $fpdi->setSourceFile($tmpFile);

                for ($i = 1; $i <= $pageCount; $i++) {
                    $tplId = $fpdi->importPage($i);
                    $size  = $fpdi->getTemplateSize($tplId);

                    $pageOrientation = $size['width'] > $size['height'] ? 'L' : 'P';

                    $fpdi->AddPage($pageOrientation, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tplId);
                }
            }

            $fileName = $this->getBulkExportFileName() . '_' . $labelPeriod . '.pdf';
            $output   = $fpdi->Output('S', $fileName);

            return response($output, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ]);

        } finally {
            foreach ($tmpFiles as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
        }
    }
}