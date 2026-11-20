<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ReportMdReject;
use App\Models\ReportMdRejectDetail;
use App\Http\Controllers\Traits\HasAuditController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\FormNumber;

class ReportMdRejectController extends Controller
{

    protected string $modelClass = ReportMdReject::class;


    public function index(Request $request)
    {
        $areas = auth()->user()->hasAnyRole(['admin', 'superadmin'])
            ? \App\Models\Area::orderBy('name')->get()
            : collect();

        $reports = ReportMdReject::query()
            ->where('is_audit', false)
            ->with(['area', 'verifications', 'details.product'])
            ->when($request->area, fn ($q, $a) => $q->where('area_uuid', $a))
            ->when($request->search, function ($q, $s) {
                $q->where(function ($w) use ($s) {
                    $w->where('line', 'like', "%{$s}%")
                    ->orWhere('shift', 'like', "%{$s}%")
                    ->orWhere('created_by', 'like', "%{$s}%")
                    ->orWhereHas('details', fn ($d) => $d->where('production_code', 'like', "%{$s}%"));
                });
            })
            ->latest('date')->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('report-md-rejects.index', compact('reports', 'areas'));
    }

    private function productOptions()
    {
        return Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->orderBy('product_name')
            ->get();
    }

    public function create()
    {
        return view('report-md-rejects.create', [
            'products' => $this->productOptions(),
            'testPieces'  => ReportMdReject::TEST_PIECES,
            'causes'      => ReportMdRejectDetail::CAUSES,
            'dispositions'=> ReportMdRejectDetail::DISPOSITIONS,
            'defaultShift'=> $this->defaultShift(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        DB::transaction(function () use ($data) {
            $report = ReportMdReject::create([
                'uuid'       => (string) Str::uuid(),
                'area_uuid'  => auth()->user()->area_uuid,
                'date'       => $data['date'],
                'shift'      => $data['shift'],
                'line'       => $data['line'],
                'created_by' => auth()->user()->name,
            ]);

            foreach ($data['verifications'] as $v) {
                $report->verifications()->create($v);
            }

            foreach ($data['details'] ?? [] as $d) {
                $report->details()->create($this->normalizeDetail($d));
            }
        });

        return redirect()->route('report-md-rejects.index')
            ->with('success', 'Laporan berhasil disimpan.');
    }

    public function show(string $uuid)
    {
        $report = $this->findReport($uuid);

        return view('report-md-rejects.show', compact('report'));
    }

    public function edit(string $uuid)
    {
        $report   = $this->findReport($uuid);
        $products = $this->productOptions();

        // Samakan product_uuid tersimpan dengan uuid hasil grup (MIN(uuid)),
        // supaya option-nya terpilih di Select2
        $uuidByName = $products->pluck('uuid', 'product_name');
        foreach ($report->details as $d) {
            if ($d->product && isset($uuidByName[$d->product->product_name])) {
                $d->product_uuid = $uuidByName[$d->product->product_name];
            }
        }

        return view('report-md-rejects.edit', [
            'report'       => $report,
            'products'     => $products,
            'testPieces'   => ReportMdReject::TEST_PIECES,
            'causes'       => ReportMdRejectDetail::CAUSES,
            'dispositions' => ReportMdRejectDetail::DISPOSITIONS,
        ]);
    }

    public function update(Request $request, string $uuid)
    {
        $report = ReportMdReject::where('uuid', $uuid)->firstOrFail();
        $data   = $request->validate($this->rules());

        DB::transaction(function () use ($report, $data) {
            $report->update([
                'date'  => $data['date'],
                'shift' => $data['shift'],
                'line'  => $data['line'],
            ]);

            // Verifikasi: 3 baris tetap, cocokkan berdasarkan test_piece
            foreach ($data['verifications'] as $v) {
                $report->verifications()->updateOrCreate(
                    ['test_piece' => $v['test_piece']],
                    ['result' => $v['result'], 'notes' => $v['notes'] ?? null]
                );
            }

            // Detail: cocokkan berdasarkan uuid (bukan hapus semua lalu buat ulang)
            $incoming = collect($data['details'] ?? []);
            $keepIds  = $incoming->pluck('uuid')->filter()->all();

            $report->details()->whereNotIn('uuid', $keepIds)->delete();

            foreach ($incoming as $d) {
                $payload = $this->normalizeDetail($d);

                if (!empty($d['uuid'])) {
                    $report->details()->where('uuid', $d['uuid'])->update($payload);
                } else {
                    $report->details()->create($payload);
                }
            }
        });

        return redirect()->route('report-md-rejects.index', $uuid)
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy(string $uuid)
    {
        ReportMdReject::where('uuid', $uuid)->firstOrFail()->delete();

        return redirect()->route('report-md-rejects.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    public function known(string $uuid)
    {
        $report = ReportMdReject::where('uuid', $uuid)->firstOrFail();
        abort_if($report->known_by, 422, 'Laporan sudah diketahui.');

        $report->update(['known_by' => auth()->user()->name]);

        return back()->with('success', 'Laporan berhasil diketahui.');
    }

    public function approve(string $uuid)
    {
        $report = ReportMdReject::where('uuid', $uuid)->firstOrFail();
        abort_if(!$report->known_by, 422, 'Laporan belum diketahui.');
        abort_if($report->approved_by, 422, 'Laporan sudah disetujui.');

        $report->update([
            'approved_by' => auth()->user()->name,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Laporan berhasil disetujui.');
    }

    /* ===================== HELPER ===================== */

    private function findReport(string $uuid): ReportMdReject
    {
        return ReportMdReject::with(['verifications', 'details.product', 'area'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    private function defaultShift(): string
    {
        return auth()->user()->hasRole('QC Inspector') && session('shift_number') && session('shift_group')
            ? session('shift_number') . '-' . session('shift_group')
            : 'NON-SHIFT';
    }

    private function rules(): array
    {
        $result = ['required', Rule::in(['detected', 'not_detected'])];

        return [
            'date'  => 'required|date',
            'shift' => 'required|string|max:50',
            'line'  => 'required|string|max:100',

            'verifications'              => 'required|array|size:' . count(ReportMdReject::TEST_PIECES),
            'verifications.*.test_piece' => ['required', Rule::in(ReportMdReject::TEST_PIECES)],
            'verifications.*.result'     => $result,
            'verifications.*.notes'      => 'nullable|string|max:255',

            'details'                         => 'nullable|array',
            'details.*.uuid'                  => 'nullable|string',
            'details.*.product_uuid'          => 'required|exists:products,uuid',
            'details.*.time'                  => 'required|date_format:H:i',
            'details.*.production_code'       => 'required|string|max:100',
            'details.*.reject_qty'            => 'required|integer|min:0',
            'details.*.recheck_result'        => ['required', Rule::in(['detected', 'not_detected'])],
            'details.*.contamination_found'   => 'required|boolean',
            'details.*.suspected_causes'      => 'nullable|array',
            'details.*.suspected_causes.*'    => Rule::in(array_keys(ReportMdRejectDetail::CAUSES)),
            'details.*.suspected_cause_other' => 'nullable|string|max:255',
            'details.*.corrective_action'     => 'nullable|string',
            'details.*.disposition'           => ['required', Rule::in(array_keys(ReportMdRejectDetail::DISPOSITIONS))],
        ];
    }

    private function normalizeDetail(array $d): array
    {
        $causes = array_values($d['suspected_causes'] ?? []);

        return [
            'product_uuid'          => $d['product_uuid'],
            'time'                  => $d['time'],
            'production_code'       => trim($d['production_code']),
            'reject_qty'            => $d['reject_qty'],
            'recheck_result'        => $d['recheck_result'],
            'contamination_found'   => (bool) $d['contamination_found'],
            'suspected_causes'      => $causes,
            // isian "Lainnya" hanya disimpan kalau opsi Lainnya dicentang
            'suspected_cause_other' => in_array('other', $causes, true)
                ? ($d['suspected_cause_other'] ?? null)
                : null,
            'corrective_action'     => $d['corrective_action'] ?? null,
            'disposition'           => $d['disposition'],
        ];
    }

    public function exportPdf(string $uuid)
    {
        $report = $this->findReport($uuid);

        $qr = fn (string $text) => 'data:image/png;base64,'
            . base64_encode(QrCode::format('png')->size(150)->generate($text));

        // QR dari kolom tersimpan di laporan, bukan dari user yang login
        $createdQr = $qr("Dibuat oleh: {$report->created_by}\nTanggal: " . $report->created_at->format('Y-m-d H:i'));

        $knownQr = $report->known_by
            ? $qr("Diketahui oleh: {$report->known_by}")
            : null;

        $approvedQr = $report->approved_by
            ? $qr("Disetujui oleh: {$report->approved_by}\nTanggal: " . \Carbon\Carbon::parse($report->approved_at)->format('Y-m-d H:i'))
            : null;

        $formNumber = FormNumber::get($report->area_uuid, 'report-md-rejects');

        $pdf = Pdf::loadView('report-md-rejects.pdf', [
            'report'     => $report,
            'createdQr'  => $createdQr,
            'knownQr'    => $knownQr,
            'approvedQr' => $approvedQr,
            'formNumber' => $formNumber,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('laporan_md_reject_' . $report->date->format('Y-m-d') . '.pdf');
    }
}