<?php
namespace App\Http\Controllers;

use App\Services\TraceabilityService;
use Illuminate\Http\Request;

class TraceabilityController extends Controller
{
    private const SHIFTS = ['1', '2', '3']; // sesuaikan dengan nilai shift di database

    public function index()
    {
        return view('traceability.index', $this->options());
    }

    public function search(Request $request, TraceabilityService $service)
    {
        $request->validate([
            'production_code' => 'nullable|string|min:2',
            'product'         => 'nullable|string',
            'shift'           => 'nullable|string',
            'date_from'       => 'nullable|date',
            'date_to'         => 'nullable|date|after_or_equal:date_from',
            'area' => 'nullable|string',
        ]);

        $filters = $this->filters($request);

        if (blank($request->production_code) && empty($filters)) {
            return back()->withErrors([
                'production_code' => 'Isi kata kunci atau pilih minimal satu filter.',
            ])->withInput();
        }

        return view('traceability.index', $this->options() + [
            'results' => $service->traceByBatch($request->production_code ?? '', $this->scoped($filters)),
            'production_code' => $request->production_code,
            'filters'         => $filters,
        ]);
    }

    public function formDetails(Request $request, TraceabilityService $service)
    {
        $request->validate([
            'module'    => 'required|string',
            'form_key'  => 'required|string',
            'search'    => 'nullable|string',
            'product'   => 'nullable|string',
            'shift'     => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
        ]);

        return response()->json(
            $service->getFormDetails(
                $request->module,
                $request->form_key,
                $request->search ?? '',
                $this->scoped($this->filters($request))
            )
        );
    }

    private function isSuperadmin(): bool
    {
        return auth()->user()?->hasRole('superadmin') ?? false;
    }

    // Filter pilihan user (untuk form, chip, dan AJAX)
    private function filters(Request $request): array
    {
        $keys = ['product', 'shift', 'date_from', 'date_to'];
        if ($this->isSuperadmin()) {
            $keys[] = 'area';
        }

        return array_filter($request->only($keys), 'filled');
    }

    private function isAuditor(): bool
    {
        return auth()->user()?->hasRole('auditor') ?? false;
    }

    // Filter untuk query: non-superadmin dipaksa ke areanya sendiri
    private function scoped(array $filters): array
    {
        if (!$this->isSuperadmin() && auth()->user()?->area_uuid) {
            $filters['area'] = auth()->user()->area_uuid;
        }

        // Auditor hanya melihat data audit, role lain hanya data operasional
        $filters['audit'] = $this->isAuditor();

        return $filters;
    }

    private function options(): array
    {
        return [
            'products' => \App\Models\Product::orderBy('product_name')->pluck('product_name')->unique()->values(),
            'shifts'   => self::SHIFTS,
            'areas'    => $this->isSuperadmin()
                ? \App\Models\Area::orderBy('name')->get(['uuid', 'name'])
                : collect(),
        ];
    }
}