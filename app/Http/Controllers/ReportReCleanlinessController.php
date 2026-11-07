<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportReCleanliness;
use App\Models\DetailRoomCleanliness;
use App\Models\DetailEquipmentCleanliness;
use App\Models\FollowupDetailRoomCleanliness;
use App\Models\FollowupDetailEquipmentCleanliness;
use App\Models\RoomElement;
use App\Models\EquipmentPart;
use App\Models\Area;
use App\Models\Room;
use App\Models\Equipment;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use App\Exports\ReCleanlinessExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Traits\HasBulkApproval;
use App\Traits\HasBulkPdfExport;
use App\Traits\HasSortableReport;
use App\Http\Controllers\Traits\HasAuditController;
use Illuminate\Validation\ValidationException;

class ReportReCleanlinessController extends Controller
{
    use HasBulkApproval, HasBulkPdfExport, HasSortableReport, HasAuditController;

    protected function auditModel(): string { return ReportReCleanliness::class; }
    protected function auditRoutePrefix(): string { return 'report-re-cleanliness'; }
    protected function auditViewPrefix(): string { return 'report_re_cleanliness'; }
    protected function auditRelations(): array
    {
        return [
            'area',
            'roomDetails.room',
            'roomDetails.element',
            'roomDetails.followups',
            'equipmentDetails.equipment',
            'equipmentDetails.part',
            'equipmentDetails.followups',
        ];
    }
    protected function auditSearchColumns(): array { return ['created_by']; }
    protected function auditDateColumn(): string { return 'date'; }
    protected function auditTimeColumn(): string { return 'created_at'; }
    protected function auditPlanColumn(): ?string { return null; }

    protected string $bulkModel = ReportReCleanliness::class;

    protected function getBulkExportModelClass(): string
    {
        return ReportReCleanliness::class;
    }

    protected function getBulkExportView(): string
    {
        return 'report_re_cleanliness.pdf';
    }

    protected function getBulkExportEagerLoad(): array
    {
        return ['roomDetails.room',
            'roomDetails.element',
            'equipmentDetails.equipment',
            'equipmentDetails.part',
            'area'];
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
        return 'laporan_re_cleanliness';
    }

    public function index(Request $request)
    {
        $query = ReportReCleanliness::operasional()->with([
            'area',
            'roomDetails.room',
            'roomDetails.element',
            'roomDetails.followups',
            'equipmentDetails.equipment',
            'equipmentDetails.part',
            'equipmentDetails.followups',
        ]);

        // Filter Area (khusus admin & superadmin)
        if (
            auth()->user()->hasAnyRole(['admin', 'superadmin']) &&
            $request->filled('area')
        ) {
            $query->where('area_uuid', $request->area);
        }

        // 🔍 Tanggal
        $query->when($request->date, function ($q) use ($request) {
            $q->whereDate('date', $request->date);
        });

        // 🔍 Global search
        $query->when($request->search, function ($q) use ($request) {

            $search = $request->search;

            $q->where(function ($qq) use ($search) {

                // 🔹 Header laporan
                $qq->where('created_by', 'like', "%{$search}%")
                    ->orWhere('known_by', 'like', "%{$search}%")
                    ->orWhere('approved_by', 'like', "%{$search}%")
                    ->orWhere('date', 'like', "%{$search}%");

                // 🔹 Area
                $qq->orWhereHas('area', function ($a) use ($search) {
                    $a->where('name', 'like', "%{$search}%");
                });

                // 🔹 Room cleanliness
                $qq->orWhereHas('roomDetails', function ($d) use ($search) {

                    $d->where('notes', 'like', "%{$search}%")
                        ->orWhere('corrective_action', 'like', "%{$search}%")
                        ->orWhere('verification', 'like', "%{$search}%")
                        ->orWhereHas('room', function ($r) use ($search) {
                            $r->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('element', function ($e) use ($search) {
                            $e->where('element_name', 'like', "%{$search}%");
                        });

                });

                // 🔹 Equipment cleanliness
                $qq->orWhereHas('equipmentDetails', function ($d) use ($search) {

                    $d->where('notes', 'like', "%{$search}%")
                        ->orWhere('corrective_action', 'like', "%{$search}%")
                        ->orWhere('verification', 'like', "%{$search}%")
                        ->orWhereHas('equipment', function ($e) use ($search) {
                            $e->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('part', function ($p) use ($search) {
                            $p->where('part_name', 'like', "%{$search}%");
                        });

                });

            });

        });

        // 🚨 Hanya Tidak OK
        $query->when($request->only_nc, function ($q) {

            $q->where(function ($qq) {

                $qq->whereHas('roomDetails', fn($d) =>
                    $d->where('verification', 'Tidak OK')
                )
                ->orWhereHas('equipmentDetails', fn($d) =>
                    $d->where('verification', 'Tidak OK')
                );

            });

        });

        // 📅 FILTER TANGGAL REPORT
        if ($request->filled('report_date')) {
            $query->whereDate('date', $request->report_date);
        }

        // 🔽 SORTING
        $this->applyReportSort($query, $request, [
            'report_date_column' => 'date',
            'production_code' => [
                'column' => 'production_code',
            ],
        ]);

        $reports = $query->paginate(10)
            ->withQueryString();

        // 🔢 Hitung ketidaksesuaian
        $reports->getCollection()->transform(function ($report) {

            $roomIssues = $report->roomDetails
                ->where('verification', 'Tidak OK')
                ->count();

            $equipmentIssues = $report->equipmentDetails
                ->where('verification', 'Tidak OK')
                ->count();

            $report->ketidaksesuaian = $roomIssues + $equipmentIssues;

            return $report;
        });

        $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
            ? Area::orderBy('name')->get()
            : collect();

        return view('report_re_cleanliness.index', compact('reports', 'areas'));
    }

    public function create()
    {
        return view('report_re_cleanliness.create', [
            'areas' => Area::all(),
            'rooms' => Room::with('elements')->get(),
            'equipments' => Equipment::with('parts', 'room')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'              => 'required|date',
            'verification_time' => 'required|date_format:H:i',
            'rooms'             => 'nullable|array',
            'equipments'        => 'nullable|array',
        ]);

        $rooms      = $request->input('rooms', []);
        $equipments = $request->input('equipments', []);

        if (empty($rooms) && empty($equipments)) {
            return back()->withInput()->with('error', 'Pilih minimal satu ruangan atau mesin/peralatan.');
        }

        $areaUuid   = Auth::user()->area_uuid;
        $time       = $request->verification_time;
        $reportUuid = (string) Str::uuid(); // selalu header baru
        $now        = now();

        // Susun data SEBELUM transaksi
        [$roomRows, $roomFollowups] = $this->buildRows(
            $rooms, Room::pluck('uuid')->all(), $reportUuid, $time, $now,
            'room_uuid', 'room_element_uuid', 'elements', 'detail_room_uuid'
        );

        [$equipmentRows, $equipmentFollowups] = $this->buildRows(
            $equipments, Equipment::pluck('uuid')->all(), $reportUuid, $time, $now,
            'equipment_uuid', 'equipment_part_uuid', 'parts', 'detail_equipment_uuid'
        );

        try {
            DB::transaction(function () use (
                $reportUuid, $areaUuid, $request,
                $roomRows, $roomFollowups, $equipmentRows, $equipmentFollowups
            ) {
                ReportReCleanliness::create([
                    'uuid'       => $reportUuid,
                    'area_uuid'  => $areaUuid,
                    'date'       => $request->date,
                    'created_by' => Auth::user()->name,
                ]);

                foreach (array_chunk($roomRows, 500) as $c)           DetailRoomCleanliness::insert($c);
                foreach (array_chunk($roomFollowups, 500) as $c)      FollowupDetailRoomCleanliness::insert($c);
                foreach (array_chunk($equipmentRows, 500) as $c)      DetailEquipmentCleanliness::insert($c);
                foreach (array_chunk($equipmentFollowups, 500) as $c) FollowupDetailEquipmentCleanliness::insert($c);
            });

            return redirect()->route('report-re-cleanliness.index')
                ->with('success', 'Laporan berhasil disimpan.');

        } catch (\Throwable $e) {
            \Log::error('Failed to store ReportReCleanliness: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Gagal menyimpan laporan. Silakan coba lagi.');
        }
    }

    /**
     * Ubah input form (rooms / equipments) menjadi row detail + row followup.
     *
     * @return array{0: array, 1: array} [$detailRows, $followupRows]
     */
    private function buildRows(
        array $data, array $allowedUuids, string $reportUuid, string $time, $now,
        string $parentCol, string $itemCol, string $itemsField, string $followupFk
    ): array {
        $rows = [];
        $followups = [];

        foreach ($data as $parentUuid => $parentData) {
            // Cegah uuid yang dimanipulasi / bukan milik area user (Room & Equipment ber-UserAreaScope)
            if (!in_array($parentUuid, $allowedUuids, true)) {
                abort(403, 'Data ruangan/mesin tidak valid.');
            }

            // Tanpa item -> 1 row untuk parent itu sendiri
            if (empty($parentData[$itemsField])) {
                $rows[] = $this->detailRow($reportUuid, $time, $now, $parentCol, $parentUuid, $itemCol, null, [
                    'condition' => $parentData['condition'] ?? 'dirty',
                ])['row'];
                continue;
            }

            foreach ($parentData[$itemsField] as $itemUuid => $item) {
                $built = $this->detailRow($reportUuid, $time, $now, $parentCol, $parentUuid, $itemCol, $itemUuid, $item);
                $rows[] = $built['row'];

                foreach ($built['followups'] as $f) {
                    $followups[] = [
                        $followupFk         => $built['row']['uuid'],
                        'notes'             => $f['notes'],
                        'corrective_action' => $f['action'],
                        'verification'      => $f['verification'],
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
            }
        }

        return [$rows, $followups];
    }

    private function detailRow(
        string $reportUuid, string $time, $now,
        string $parentCol, string $parentUuid, string $itemCol, ?string $itemUuid, array $item
    ): array {
        $isClean      = ($item['condition'] ?? null) === 'clean';
        $verification = $item['verification'] ?? null;

        if (!$isClean && !in_array($verification, ['OK', 'Tidak OK'], true)) {
            throw ValidationException::withMessages([
                'rooms' => 'Ada item kotor yang verifikasinya belum dipilih.',
            ]);
        }

        $row = [
            'uuid'              => (string) Str::uuid(),
            'report_re_uuid'    => $reportUuid,
            $parentCol          => $parentUuid,
            $itemCol            => $itemUuid,
            'condition'         => $isClean ? 'clean' : 'dirty',
            'notes'             => $isClean ? null : ($item['notes'] ?? null),
            'corrective_action' => $isClean ? null : ($item['corrective_action'] ?? null),
            'verification'      => $isClean ? 'OK' : $verification,
            'verification_time' => $time,
            'created_at'        => $now,
            'updated_at'        => $now,
        ];

        // Followup hanya untuk item kotor yang verifikasi utamanya "Tidak OK"
        $followups = [];
        if (!$isClean && $verification === 'Tidak OK') {
            foreach (($item['followups'] ?? []) as $f) {
                $notes  = trim($f['notes'] ?? '');
                $action = trim($f['action'] ?? '');
                $ver    = $f['verification'] ?? null;

                if ($notes === '' && $action === '' && !$ver) {
                    continue; // baris kosong dilewati
                }

                $followups[] = [
                    'notes'        => $notes ?: null,
                    'action'       => $action ?: null,
                    'verification' => in_array($ver, ['OK', 'Tidak OK'], true) ? $ver : null,
                ];
            }
        }

        return ['row' => $row, 'followups' => $followups];
    }

    public function destroy($uuid)
    {
        $report = ReportReCleanliness::where('uuid', $uuid)->firstOrFail();

        try {
            DB::transaction(function () use ($report) {
                // Kalau yang dihapus laporan asli, salinan audit-nya ikut dihapus
                $targets = $report->is_audit
                    ? collect([$report])
                    : ReportReCleanliness::where('source_uuid', $report->uuid)->get()->prepend($report);

                foreach ($targets as $r) {
                    $roomIds = DetailRoomCleanliness::where('report_re_uuid', $r->uuid)->pluck('uuid');
                    $equipmentIds = DetailEquipmentCleanliness::where('report_re_uuid', $r->uuid)->pluck('uuid');

                    FollowupDetailRoomCleanliness::whereIn('detail_room_uuid', $roomIds)->delete();
                    FollowupDetailEquipmentCleanliness::whereIn('detail_equipment_uuid', $equipmentIds)->delete();
                    DetailRoomCleanliness::whereIn('uuid', $roomIds)->delete();
                    DetailEquipmentCleanliness::whereIn('uuid', $equipmentIds)->delete();

                    $r->delete();
                }
            });
        } catch (\Throwable $e) {
            \Log::error('Failed to delete ReportReCleanliness: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus laporan.');
        }

        return redirect()
            ->route($report->is_audit ? 'report-re-cleanliness.audit' : 'report-re-cleanliness.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    public function approve($id)
    {
        $report = ReportReCleanliness::findOrFail($id);
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
        $report = ReportReCleanliness::findOrFail($id);
        $user = Auth::user();

        if ($report->known_by) {
            return redirect()->back()->with('error', 'Laporan sudah diketahui.');
        }

        $report->known_by = $user->name;
        $report->save();

        return redirect()->back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function exportPdf($uuid)
    {
        $report = ReportReCleanliness::with([
            'roomDetails.room',
            'roomDetails.element',
            'roomDetails.followups',
            'equipmentDetails.equipment',
            'equipmentDetails.part',
            'equipmentDetails.followups',
            'area'
        ])->where('uuid', $uuid)->firstOrFail();
    
        // Generate QR untuk created_by
        $createdInfo = "Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i');
        $createdQrImage = QrCode::format('png')->size(100)->generate($createdInfo);
        $createdQrBase64 = 'data:image/png;base64,' . base64_encode($createdQrImage);
    
        // Generate QR untuk approved_by
        $approvedInfo = $report->approved_by
            ? "Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i')
            : "Belum disetujui";
        $approvedQrImage = QrCode::format('png')->size(100)->generate($approvedInfo);
        $approvedQrBase64 = 'data:image/png;base64,' . base64_encode($approvedQrImage);
    
        // Generate QR untuk known_by
        $knownInfo = $report->known_by
            ? "Diketahui oleh: {$report->known_by}"
            : "Belum disetujui";
        $knownQrImage = QrCode::format('png')->size(100)->generate($knownInfo);
        $knownQrBase64 = 'data:image/png;base64,' . base64_encode($knownQrImage);
    
        $formNumber = \App\Models\FormNumber::get($report->area->uuid, 'report_re_cleanliness');
    
        $pdf = Pdf::loadView('report_re_cleanliness.pdf', [
            'report' => $report,
            'createdQr' => $createdQrBase64,
            'approvedQr' => $approvedQrBase64,
            'knownQr' => $knownQrBase64,
            'formNumber' => $formNumber,
        ])
            ->setPaper('A4', 'portrait');
    
        return $pdf->stream('laporan_kebersihan_' . $report->date . '.pdf');
    }

    public function edit($uuid)
    {
        $report = ReportReCleanliness::with('roomDetails.followups', 'equipmentDetails.followups')
            ->where('uuid', $uuid)->firstOrFail();

        return view('report_re_cleanliness.edit', [
            'report'      => $report,
            'sessionTime' => $report->verification_times[0] ?? null,
            'rooms'       => Room::with('elements')->get(),
            'equipments' => Equipment::with('parts', 'room')->get(),
        ]);
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'date'              => 'required|date',
            'verification_time' => 'required|date_format:H:i',
            'original_time'     => 'nullable|date_format:H:i',
            'rooms'             => 'nullable|array',
            'equipments'        => 'nullable|array',
        ]);

        $report = ReportReCleanliness::where('uuid', $uuid)->firstOrFail();

        if ($report->approved_by) {
            return back()->withInput()->with('error', 'Laporan sudah disetujui, tidak bisa diubah.');
        }

        $rooms      = $request->input('rooms', []);
        $equipments = $request->input('equipments', []);

        if (empty($rooms) && empty($equipments)) {
            return back()->withInput()->with('error', 'Pilih minimal satu ruangan atau mesin/peralatan.');
        }

        $now  = now();
        $time = $request->verification_time;
        $orig = $request->original_time; // jam sesi sebelum diedit (null = data lama tanpa jam)

        // Susun data sebelum transaksi
        [$roomRows, $roomFollowups] = $this->buildRows(
            $rooms, Room::pluck('uuid')->all(), $report->uuid, $time, $now,
            'room_uuid', 'room_element_uuid', 'elements', 'detail_room_uuid'
        );

        [$equipmentRows, $equipmentFollowups] = $this->buildRows(
            $equipments, Equipment::pluck('uuid')->all(), $report->uuid, $time, $now,
            'equipment_uuid', 'equipment_part_uuid', 'parts', 'detail_equipment_uuid'
        );

        // Jam sama dengan sesi lain? Jangan sampai dua sesi bertabrakan
        $otherTimes = array_diff($report->verification_times, [$orig]);
        if (in_array($time, $otherTimes, true)) {
            return back()->withInput()->with('error', "Sesi jam $time sudah ada di laporan ini.");
        }

        try {
            DB::transaction(function () use (
                $report, $request, $orig,
                $roomRows, $roomFollowups, $equipmentRows, $equipmentFollowups
            ) {
                $inSession = function ($d) use ($orig) {
                    return $orig
                        ? substr((string) $d->verification_time, 0, 5) === $orig
                        : empty($d->verification_time);
                };

                // Hanya detail pada sesi yang diedit
                $roomIds = DetailRoomCleanliness::where('report_re_uuid', $report->uuid)
                    ->get()->filter($inSession)->pluck('uuid');
                $equipmentIds = DetailEquipmentCleanliness::where('report_re_uuid', $report->uuid)
                    ->get()->filter($inSession)->pluck('uuid');

                FollowupDetailRoomCleanliness::whereIn('detail_room_uuid', $roomIds)->delete();
                FollowupDetailEquipmentCleanliness::whereIn('detail_equipment_uuid', $equipmentIds)->delete();
                DetailRoomCleanliness::whereIn('uuid', $roomIds)->delete();
                DetailEquipmentCleanliness::whereIn('uuid', $equipmentIds)->delete();

                $report->update([
                    'date'       => $request->date,
                    'updated_by' => Auth::user()->name,
                ]);

                foreach (array_chunk($roomRows, 500) as $c)           DetailRoomCleanliness::insert($c);
                foreach (array_chunk($roomFollowups, 500) as $c)      FollowupDetailRoomCleanliness::insert($c);
                foreach (array_chunk($equipmentRows, 500) as $c)      DetailEquipmentCleanliness::insert($c);
                foreach (array_chunk($equipmentFollowups, 500) as $c) FollowupDetailEquipmentCleanliness::insert($c);
            });

            return redirect()
                ->route($report->is_audit ? 'report-re-cleanliness.audit' : 'report-re-cleanliness.index')
                ->with('success', $report->is_audit
                    ? 'Data audit berhasil diperbarui.'
                    : 'Data berhasil diperbarui.');

        } catch (\Throwable $e) {
            \Log::error('Failed to update ReportReCleanliness: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Gagal memperbarui laporan. Silakan coba lagi.');
        }
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
    
        $reports = ReportReCleanliness::operasional()
            ->with([
                'roomDetails.room',
                'roomDetails.element',
                'equipmentDetails.equipment',
                'equipmentDetails.part',
            ])
            ->where('area_uuid', auth()->user()->area_uuid)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('date')
            ->get();
    
        $filename = 'Kebersihan_RE_'
            . $dateFrom->format('Ymd') . '_'
            . $dateTo->format('Ymd') . '.xlsx';
    
        return Excel::download(new ReCleanlinessExport($reports, $periodLabel), $filename);
    }
}