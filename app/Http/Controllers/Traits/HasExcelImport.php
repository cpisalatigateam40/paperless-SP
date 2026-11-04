<?php

namespace App\Http\Controllers\Traits;

use App\Services\Excel\ExcelImportService;
use App\Services\Excel\ExcelTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait HasExcelImport
{
    public function downloadTemplate()
    {
        try {
            return (new ExcelTemplateService())->generateWithDropdown(
                $this->getImportModelClass(),
                $this->getTemplateFilename()
            );
        } catch (\Throwable $e) {
            Log::error('Template download error: ' . $e->getMessage(), ['line' => $e->getLine()]);
            return redirect()->back()->with('error', 'Gagal download template: ' . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240', // tanpa spasi setelah koma!
        ]);

        try {
            $results = (new ExcelImportService())->import(
                $request->file('file'),
                $this->getImportModelClass(),
                $this->getAdditionalImportData()
            );

            $errors = array_slice($results['errors'], 0, 50); // batasi ukuran session

            if ($results['success'] > 0) {
                $message = "Import berhasil: {$results['success']} data diimport";
                if ($results['failed'] > 0) {
                    $message .= ", {$results['failed']} data gagal";
                }
                return redirect()->back()->with(['success' => $message, 'import_errors' => $errors]);
            }

            return redirect()->back()->with([
                'error'         => 'Import gagal: tidak ada data yang berhasil disimpan',
                'import_errors' => $errors,
            ]);
        } catch (\Throwable $e) {
            Log::error('Import error: ' . $e->getMessage(), ['line' => $e->getLine()]);
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    abstract protected function getImportModelClass(): string;

    abstract protected function getTemplateFilename(): string;

    // Data yang ditambahkan ke setiap baris (bukan dari Excel).
    // Override di controller bila perlu tenant/cabang/plant, dll.
    protected function getAdditionalImportData(): array
    {
        return [
            'user_id' => auth()->id(),
        ];
    }
}