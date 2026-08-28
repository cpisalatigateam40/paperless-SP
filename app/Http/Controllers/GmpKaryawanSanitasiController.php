<?php
// app/Http/Controllers/GmpKaryawanSanitasiController.php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGmpHeaderRequest;
use App\Models\GmpEmployeeCheck;
use App\Models\GmpHeader;
use App\Models\GmpSanitationCheck;
use App\Models\GmpWaktuPemeriksaan;
use App\Models\Area;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\FormNumber;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Traits\HasBulkPdfExport;
use App\Traits\HasSortableReport;
use App\Traits\HasBulkApproval;
use App\Exports\GmpExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class GmpKaryawanSanitasiController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport, HasSortableReport;

    protected string $bulkModel = GmpHeader::class;

    protected function getBulkExportModelClass(): string
    {
        return GmpHeader::class;
    }

    protected function getBulkExportView(): string
    {
        return 'gmp.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['area',
            'waktuPemeriksaans' => fn ($q) => $q->orderBy('waktu_ke'),
            'waktuPemeriksaans.employeeChecks.section',
            'waktuPemeriksaans.sanitationChecks.section',];
    }

    protected function getBulkExportExtraData($report): array
    {
        $createdInfo = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQr = QrCode::format('png')->size(150)->generate($createdInfo);

        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQr = QrCode::format('png')->size(150)->generate($approvedInfo);

        $knownInfo = $report->known_by ? "Diketahui oleh: {$report->known_by}" : "Belum disetujui";
        $knownQr = QrCode::format('png')->size(150)->generate($knownInfo);

        return [
            'createdQr'  => 'data:image/png;base64,' . base64_encode($createdQr),
            'approvedQr' => 'data:image/png;base64,' . base64_encode($approvedQr),
            'knownQr'    => 'data:image/png;base64,' . base64_encode($knownQr),
        ];
    }

    protected function getBulkExportFileName(): string
    {
        return 'gmp';
    }

    protected array $sanitationItemList = [
        ['item' => 'Foot Basin', 'chlorine_std' => 200],
        ['item' => 'Hand Basin', 'chlorine_std' => 50],
    ];



    public function index(Request $request)
    {
        $query = GmpHeader::with([
            'area',
            'waktuPemeriksaans.employeeChecks.section',
            'waktuPemeriksaans.sanitationChecks.section',
        ]);

        // 🔒 AREA FILTER (admin/superadmin only)
        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        // 🔽 SECTION FILTER (GMP Karyawan / Sanitasi Area)
        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // 🔹 HEADER REPORT
                $q->where('shift', 'like', "%{$search}%")
                    ->orWhere('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%");

                // 🔹 AREA (relasi area_uuid)
                $q->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                // 🔹 GMP KARYAWAN CHECKS
                $q->orWhereHas('waktuPemeriksaans.employeeChecks', function ($e) use ($search) {
                    $e->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('tindakan_koreksi', 'like', "%{$search}%")
                        ->orWhereHas('section', function ($s) use ($search) {
                            $s->where('section_name', 'like', "%{$search}%");
                        });
                });

                // 🔹 SANITASI AREA CHECKS
                $q->orWhereHas('waktuPemeriksaans.sanitationChecks', function ($s) use ($search) {
                    $s->where('item_verifikasi', 'like', "%{$search}%")
                        ->orWhere('tindakan_koreksi', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%")
                        ->orWhereHas('section', function ($sec) use ($search) {
                            $sec->where('section_name', 'like', "%{$search}%");
                        });
                });
            });
        }

        // 📅 FILTER TANGGAL REPORT
        if ($request->filled('report_date')) {
            $query->whereDate('date', $request->report_date);
        }

        // 🔽 SORTING
        $this->applyReportSort($query, $request, [
            'report_date_column' => 'date',
        ]);

        $headers = $query->latest('date')->paginate(10)->withQueryString();

        if (auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            $areas = Area::orderBy('name')->get();
        } else {
            $areas = collect();
        }

        return view('gmp.index', compact('headers', 'areas'));
    }

    public function create()
    {
        return view('gmp.create', [
            'sections' => Section::orderBy('section_name')->get(['uuid', 'section_name']),
            'sanitationItemList' => $this->sanitationItemList,
        ]);
    }

    public function store(StoreGmpHeaderRequest $request)
    {
        $validated = $request->validated();

        $shift = auth()->user()->hasRole('QC Inspector')
            ? session('shift_number') . '-' . session('shift_group')
            : ($validated['shift'] ?? 'NON-SHIFT');

        DB::transaction(function () use ($validated, $shift) {
            $header = GmpHeader::create([
                'uuid' => Str::uuid(),
                'area_uuid' => Auth::user()->area_uuid ?? null,
                'date' => $validated['date'],
                'shift' => $shift,
                'section' => $validated['section'],
                'created_by' => Auth::user()->name ?? 'system',
                'known_by' => $validated['known_by'] ?? null,
                'approved_by' => $validated['approved_by'] ?? null,
            ]);

            foreach ($validated['waktu'] as $waktuIndex => $waktuData) {
                $waktu = GmpWaktuPemeriksaan::create([
                    'uuid' => Str::uuid(),
                    'header_uuid' => $header->uuid,
                    'waktu_ke' => $waktuIndex + 1,
                    'jam_pemeriksaan' => $waktuData['jam_pemeriksaan'] ?? null,
                    'catatan' => $waktuData['catatan'] ?? null,
                ]);

                if ($header->section === 'gmp_karyawan') {
                    foreach ($waktuData['employees'] ?? [] as $employee) {
                        GmpEmployeeCheck::create([
                            'uuid' => Str::uuid(),
                            'waktu_uuid' => $waktu->uuid,
                            ...$employee,
                        ]);
                    }
                } else {
                    foreach ($waktuData['sanitations'] ?? [] as $sanitation) {
                        GmpSanitationCheck::create([
                            'uuid' => Str::uuid(),
                            'waktu_uuid' => $waktu->uuid,
                            ...$sanitation,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('gmp.index')->with('success', 'Data berhasil disimpan.');
    }

    public function show(GmpHeader $gmpHeader)
    {
        $gmpHeader->load([
            'waktuPemeriksaans.employeeChecks.section',
            'waktuPemeriksaans.sanitationChecks.section',
        ]);

        return view('gmp.show', compact('gmpHeader'));
    }

    public function edit(GmpHeader $gmpHeader)
    {
        $gmpHeader->load([
            'waktuPemeriksaans.employeeChecks.section',
            'waktuPemeriksaans.sanitationChecks.section',
        ]);

        return view('gmp.edit', [
            'gmpHeader' => $gmpHeader,
            'sections' => Section::orderBy('section_name')->get(['uuid', 'section_name']),
            'sanitationItemList' => $this->sanitationItemList,
        ]);
    }

    public function update(StoreGmpHeaderRequest $request, GmpHeader $gmpHeader)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $gmpHeader) {
            $gmpHeader->update([
                'date' => $validated['date'],
                'shift' => $validated['shift'] ?? $gmpHeader->shift,
                'known_by' => $validated['known_by'] ?? $gmpHeader->known_by,
                'approved_by' => $validated['approved_by'] ?? $gmpHeader->approved_by,
            ]);

            $gmpHeader->waktuPemeriksaans()->delete();

            foreach ($validated['waktu'] as $waktuIndex => $waktuData) {
                $waktu = GmpWaktuPemeriksaan::create([
                    'uuid' => Str::uuid(),
                    'header_uuid' => $gmpHeader->uuid,
                    'waktu_ke' => $waktuIndex + 1,
                    'jam_pemeriksaan' => $waktuData['jam_pemeriksaan'] ?? null,
                    'catatan' => $waktuData['catatan'] ?? null,
                ]);

                if ($gmpHeader->section === 'gmp_karyawan') {
                    foreach ($waktuData['employees'] ?? [] as $employee) {
                        GmpEmployeeCheck::create([
                            'uuid' => Str::uuid(),
                            'waktu_uuid' => $waktu->uuid,
                            ...$employee,
                        ]);
                    }
                } else {
                    foreach ($waktuData['sanitations'] ?? [] as $sanitation) {
                        GmpSanitationCheck::create([
                            'uuid' => Str::uuid(),
                            'waktu_uuid' => $waktu->uuid,
                            ...$sanitation,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('gmp.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(GmpHeader $gmpHeader)
    {
        $gmpHeader->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }

    public function exportPdf(GmpHeader $gmpHeader)
    {
        $gmpHeader->load([
            'area',
            'waktuPemeriksaans' => fn ($q) => $q->orderBy('waktu_ke'),
            'waktuPemeriksaans.employeeChecks.section',
            'waktuPemeriksaans.sanitationChecks.section',
        ]);

        $createdInfo = "Diperiksa oleh: {$gmpHeader->created_by}\nTanggal: " . $gmpHeader->created_at->format('Y-m-d H:i');
        $createdQrImage = QrCode::format('png')->size(150)->generate($createdInfo);
        $createdQr = 'data:image/png;base64,' . base64_encode($createdQrImage);

        $knownInfo = $gmpHeader->known_by
            ? "Diketahui oleh: {$gmpHeader->known_by}"
            : "Belum diketahui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQr = 'data:image/png;base64,' . base64_encode($knownQrImage);

        $approvedInfo = $gmpHeader->approved_by
            ? "Disetujui oleh: {$gmpHeader->approved_by}\nTanggal: " . optional($gmpHeader->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQrImage = QrCode::format('png')->size(150)->generate($approvedInfo);
        $approvedQr = 'data:image/png;base64,' . base64_encode($approvedQrImage);

        $formNumber = FormNumber::get($gmpHeader->area_uuid, 'gmp');

        $pdf = Pdf::loadView('gmp.pdf', [
            'report' => $gmpHeader,
            'createdQr' => $createdQr,
            'knownQr' => $knownQr,
            'approvedQr' => $approvedQr,
            'formNumber' => $formNumber,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('gmp_' . $gmpHeader->uuid . '.pdf');
    }

    /**
     * Known by
     */
    public function known($id)
    {
        $report = GmpHeader::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Laporan sudah diketahui.'
                );
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()
            ->back()
            ->with(
                'success',
                'Laporan berhasil diketahui.'
            );
    }

    /**
     * Approve
     */
    public function approve($id)
    {
        $report = GmpHeader::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Laporan sudah disetujui.'
                );
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()
            ->back()
            ->with(
                'success',
                'Laporan berhasil disetujui.'
            );
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:range,month',
            'date_from'   => 'required_if:filter_type,range|nullable|date',
            'date_to'     => 'required_if:filter_type,range|nullable|date|after_or_equal:date_from',
            'month'       => 'required_if:filter_type,month|nullable|date_format:Y-m',
        ]);

        if ($request->filter_type === 'month') {
            $dateFrom    = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
            $dateTo      = $dateFrom->copy()->endOfMonth();
            $periodLabel = $dateFrom->translatedFormat('F Y');
        } else {
            $dateFrom    = Carbon::parse($request->date_from)->startOfDay();
            $dateTo      = Carbon::parse($request->date_to)->endOfDay();
            $periodLabel = $dateFrom->format('d/m/Y') . ' – ' . $dateTo->format('d/m/Y');
        }

        $headers = GmpHeader::with([
                'waktuPemeriksaans.employeeChecks.section',
                'waktuPemeriksaans.sanitationChecks.section',
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->get();

        $filename = 'GMP_Karyawan_Sanitasi_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';

        return Excel::download(new GmpExport($headers, $periodLabel), $filename);
    }
}