<?php

namespace App\Http\Controllers;

use App\Models\ReportAlatVerification;
use App\Models\DetailAlatVerification;
use App\Models\Scale;
use App\Models\Area;
use App\Models\Thermometer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\FormNumber;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AlatVerificationExport;
use App\Traits\HasSortableReport;

class ReportAlatVerificationController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport, HasSortableReport;
    protected string $bulkModel = ReportAlatVerification::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportAlatVerification::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_alat_verifications.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['area',
            'details.alat',];
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
        return 'laporan_alat_verif';
    }

    public function index(Request $request)
    {
        $query = ReportAlatVerification::with('details.alat');

        // Filter Area (khusus admin & superadmin)
        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
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

                // 🔹 AREA
                $q->orWhereHas('area', fn ($a) => $a->where('name', 'like', "%{$search}%"));

                // 🔹 DETAIL ALAT VERIFICATION
                $q->orWhereHas('details', function ($d) use ($search) {
                    $d->where('titik_ukur', 'like', "%{$search}%")
                    ->orWhere('nilai_baca', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")

                    // 🔹 ALAT (polymorphic: Scale / Thermometer)
                    ->orWhereHasMorph(
                        'alat',
                        [\App\Models\Scale::class, \App\Models\Thermometer::class],
                        function ($q2, $type) use ($search) {
                            $q2->where('code', 'like', "%{$search}%")
                                ->orWhere('type', 'like', "%{$search}%")
                                ->orWhere('brand', 'like', "%{$search}%");

                            if ($type === \App\Models\Scale::class) {
                                $q2->orWhere('owner', 'like', "%{$search}%");
                            }
                        }
                    );
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
            'production_code' => [
                'relation' => 'details',
                'column' => 'production_code',
            ],
        ]);

        $reports = $query->paginate(10)->withQueryString();
        $scales = Scale::all();
        $thermometers = Thermometer::all();

        $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
            ? Area::orderBy('name')->get()
            : collect();

        return view('report_alat_verifications.index', compact('reports', 'scales', 'thermometers', 'areas'));
    }

    public function create()
    {
        $scales = Scale::all();
        $thermometers = Thermometer::all();

        return view('report_alat_verifications.form', compact('scales', 'thermometers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.alat_type' => 'required|in:scale,thermometer',
            'items.*.alat_uuid' => 'required|uuid',
            'items.*.titik_ukur' => 'required|string',
            'items.*.nilai_baca' => 'required|numeric',
            'items.*.check_time' => 'nullable',
            'items.*.notes' => 'nullable|string',
        ]);

        // alat_uuid tidak bisa divalidasi pakai rule exists: biasa karena tabelnya
        // beda tergantung alat_type, jadi dicek manual per baris
        foreach ($request->items as $item) {
            $exists = $item['alat_type'] === 'scale'
                ? Scale::where('uuid', $item['alat_uuid'])->exists()
                : Thermometer::where('uuid', $item['alat_uuid'])->exists();

            if (! $exists) {
                return back()
                    ->withErrors(['items' => 'Salah satu alat tidak valid atau tidak ditemukan di area Anda.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($request) {
            $shift = auth()->user()->hasRole('QC Inspector')
                ? session('shift_number') . '-' . session('shift_group')
                : ($request->shift ?? 'NON-SHIFT');

            $report = ReportAlatVerification::create([
                'area_uuid' => Auth::user()->area_uuid,
                'date' => $request->date,
                'shift' => $shift,
                'created_by' => Auth::user()->name,
            ]);

            foreach ($request->items as $item) {
                DetailAlatVerification::create([
                    'report_alat_verification_uuid' => $report->uuid,
                    'alat_type' => $item['alat_type'],
                    'alat_uuid' => $item['alat_uuid'],
                    'titik_ukur' => $item['titik_ukur'],
                    'nilai_baca' => $item['nilai_baca'],
                    'check_time' => $item['check_time'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('report-alat-verifications.index')
            ->with('success', 'Laporan verifikasi alat berhasil disimpan.');
    }

    public function edit(string $uuid)
    {
        $report = ReportAlatVerification::with('details')->findOrFail($uuid);
        $scales = Scale::all();
        $thermometers = Thermometer::all();

        return view('report_alat_verifications.form', compact('report', 'scales', 'thermometers'));
    }

    public function update(Request $request, string $uuid)
    {
        $report = ReportAlatVerification::findOrFail($uuid);

        $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.alat_type' => 'required|in:scale,thermometer',
            'items.*.alat_uuid' => 'required|uuid',
            'items.*.titik_ukur' => 'required|string',
            'items.*.nilai_baca' => 'required|numeric',
            'items.*.check_time' => 'nullable',
            'items.*.notes' => 'nullable|string',
        ]);

        foreach ($request->items as $item) {
            $exists = $item['alat_type'] === 'scale'
                ? Scale::where('uuid', $item['alat_uuid'])->exists()
                : Thermometer::where('uuid', $item['alat_uuid'])->exists();

            if (! $exists) {
                return back()
                    ->withErrors(['items' => 'Salah satu alat tidak valid atau tidak ditemukan di area Anda.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($request, $report) {
            $report->update([
                'date' => $request->date,
                'shift' => $request->shift ?? $report->shift,
            ]);

            // Form dikirim ulang penuh setiap kali, jadi detail lama diganti total
            $report->details()->delete();

            foreach ($request->items as $item) {
                DetailAlatVerification::create([
                    'report_alat_verification_uuid' => $report->uuid,
                    'alat_type' => $item['alat_type'],
                    'alat_uuid' => $item['alat_uuid'],
                    'titik_ukur' => $item['titik_ukur'],
                    'nilai_baca' => $item['nilai_baca'],
                    'check_time' => $item['check_time'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('report-alat-verifications.index')
            ->with('success', 'Laporan verifikasi alat berhasil diperbarui.');
    }

    public function show(string $uuid)
    {
        $report = ReportAlatVerification::with('details.alat')->findOrFail($uuid);

        return view('report_alat_verifications.show', compact('report'));
    }

    public function destroy(string $uuid)
    {
        $report = ReportAlatVerification::findOrFail($uuid);
        $report->delete(); // detail ikut kehapus lewat cascadeOnDelete di FK

        return redirect()
            ->route('report-alat-verifications.index')
            ->with('success', 'Laporan verifikasi alat berhasil dihapus.');
    }

    public function exportPdf($uuid)
    {
        $report = ReportAlatVerification::with([
            'area',
            'details.alat',
        ])->where('uuid', $uuid)->firstOrFail();

        $createdInfo = "Diperiksa oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQrImage = QrCode::format('png')->size(150)->generate($createdInfo);
        $createdQr = 'data:image/png;base64,' . base64_encode($createdQrImage);

        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum diketahui";
        $knownQrImage = QrCode::format('png')->size(150)->generate($knownInfo);
        $knownQr = 'data:image/png;base64,' . base64_encode($knownQrImage);

        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . optional($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQrImage = QrCode::format('png')->size(150)->generate($approvedInfo);
        $approvedQr = 'data:image/png;base64,' . base64_encode($approvedQrImage);

        $formNumber = FormNumber::get($report->area->uuid, 'report-alat-verifications');

        $pdf = Pdf::loadView('report_alat_verifications.pdf', [
            'report' => $report,
            'createdQr' => $createdQr,
            'knownQr' => $knownQr,
            'approvedQr' => $approvedQr,
            'formNumber' => $formNumber,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('verifikasi_alat_ukur_' . $report->uuid . '.pdf');
    }

    public function addDetail(Request $request, string $uuid)
    {
        $report = ReportAlatVerification::findOrFail($uuid);

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.alat_type' => 'required|in:scale,thermometer',
            'items.*.alat_uuid' => 'required|uuid',
            'items.*.titik_ukur' => 'required|string',
            'items.*.nilai_baca' => 'required|numeric',
            'items.*.check_time' => 'nullable',
            'items.*.notes' => 'nullable|string',
        ]);

        foreach ($request->items as $item) {
            $exists = $item['alat_type'] === 'scale'
                ? Scale::where('uuid', $item['alat_uuid'])->exists()
                : Thermometer::where('uuid', $item['alat_uuid'])->exists();

            if (! $exists) {
                return back()->withErrors(['items' => 'Salah satu alat tidak valid.'])->withInput();
            }
        }

        DB::transaction(function () use ($request, $report) {
            foreach ($request->items as $item) {
                DetailAlatVerification::create([
                    'report_alat_verification_uuid' => $report->uuid,
                    'alat_type' => $item['alat_type'],
                    'alat_uuid' => $item['alat_uuid'],
                    'titik_ukur' => $item['titik_ukur'],
                    'nilai_baca' => $item['nilai_baca'],
                    'check_time' => $item['check_time'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('report-alat-verifications.index')
            ->with('success', 'Detail alat berhasil ditambahkan.');
    }

    public function approve($id)
    {
        $report = ReportAlatVerification::findOrFail($id);
        $user = Auth::user();

        if ($report->approved_by) {
            return redirect()->back()->with('error', 'Laporan sudah disetujui.');
        }

        $report->approved_by = $user->name;
        $report->approved_at = now();
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function known($id)
    {
        $report = ReportAlatVerification::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
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

        $suffix = $dateFrom->format('Ymd') . '_' . $dateTo->format('Ymd');

        $reports = ReportAlatVerification::query()
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->orderBy('shift')
            ->with('details.alat')
            ->get();

        return Excel::download(new AlatVerificationExport($reports, $periodLabel), "Verifikasi_Alat_{$suffix}.xlsx");
    }
}