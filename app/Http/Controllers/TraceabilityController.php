<?php
namespace App\Http\Controllers;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;
class TraceabilityController extends Controller
{
    public function index()
    {
        return view('traceability.index');
    }
    public function search(Request $request, TraceabilityService $service)
    {
        $request->validate([
            'production_code' => 'nullable|string|min:2',
            'date' => 'nullable|date',
        ]);
        if (blank($request->production_code) && blank($request->date)) {
            return back()->withErrors([
                'production_code' => 'Masukkan kata kunci atau pilih tanggal.'
            ]);
        }
//         dd(
//     \App\Models\DetailMetalDetector::with('report')
//         ->where('production_code', 'like', '%QF26801AAO%')
//         ->get()
//         ->map(fn($d) => [
//             'detail_id' => $d->id,
//             'report_id' => $d->report->id ?? null,
//             'report_uuid' => $d->report->uuid ?? null,
//             'date' => $d->report->date ?? null,
//             'shift' => $d->report->shift ?? null,
//             'hour' => $d->hour,
//         ])
// );
        return view('traceability.index', [
            'results' => $service->traceByBatch(
                $request->production_code ?? '',

                $request->date
            ),

            'production_code' => $request->production_code,
            'date' => $request->date,
        ]);
    }
    public function formDetails(Request $request, TraceabilityService $service)
    {
        $request->validate([
            'module' => 'required|string',
            'form_key' => 'required|string',
            'search' => 'nullable|string',
            'date' => 'nullable|date',
        ]);
        return response()->json(
            $service->getFormDetails($request->module, $request->form_key, $request->search

                ?? '', $request->date)
        );
    }
}