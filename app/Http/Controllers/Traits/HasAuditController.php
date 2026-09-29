<?php
namespace App\Http\Controllers\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
trait HasAuditController
{
    abstract protected function auditModel(): string;
    abstract protected function auditRoutePrefix(): string;
    abstract protected function auditViewPrefix(): string;
    /**

    Panduan Fitur Audit Form — Halaman 4
    * Smart Copy & Edit Audit: Auto copy saat edit jika belum ada versi audit
    */
    public function copyToAudit($uuid)
    {
        $model = $this->auditModel();
        $item = $model::where('uuid', $uuid)->firstOrFail();
        if ($item->is_audit) {
            return redirect()->route("{$this->auditRoutePrefix()}.edit", $item->uuid);
        }
        if ($item->auditVersion) {
            return redirect()->route("{$this->auditRoutePrefix()}.edit", $item->auditVersion->uuid);
        }
        $clone = $item->copyToAudit();
        return redirect()->route("{$this->auditRoutePrefix()}.edit", $clone->uuid)
            ->with('info', 'Salinan data audit berhasil dibuat. Silakan lakukan penyesuaian.');
    }
    /**
     * Index Audit dengan Audit-First Smart Query Filter
     */
    public function auditIndex(Request $request)
    {
        $model = $this->auditModel();
        $user = Auth::user();
        $search = $request->get('search');
        $perPage = $request->get('per_page', 5);
        // Ambil daftar source_uuid yang sudah di-copy ke audit (Bebas Error Collation)
        $auditedSourceUuids = $model::where('is_audit', true)
            ->whereNotNull('source_uuid')
            ->pluck('source_uuid')
            ->toArray();
        $query = $model::with($this->auditRelations())
            ->where(function ($q) use ($auditedSourceUuids) {
                $q->where('is_audit', true)
                    ->orWhere(function ($subQ) use ($auditedSourceUuids) {
                        $subQ->where('is_audit', false);
                        if (!empty($auditedSourceUuids)) {
                            $subQ->whereNotIn('uuid', $auditedSourceUuids);
                        }
                    });
            })
            ->when($user->role !== 'superadmin', function ($q) use ($user) {
                $q->where($this->auditPlanColumn(), $user->id_plan);
            });
        if ($search) {
            $query->where(function ($q) use ($search) {
                foreach ($this->auditSearchColumns() as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $search . '%');
                }
            });
        }
        $records = $query->orderBy($this->auditDateColumn(), 'desc')
            ->orderBy($this->auditTimeColumn(), 'desc')
            ->paginate($perPage);
        return view("{$this->auditViewPrefix()}.index_audit", compact('records', 'search', 'perPage'));
    }
    /**
     * AJAX Search untuk Halaman Audit
     */
    public function auditSearchAjax(Request $request)
    {
        try {
            $model = $this->auditModel();
            $user = Auth::user();
            $search = $request->get('search');
            $perPage = (int) $request->get('per_page', 5);
            $page = (int) $request->get('page', 1);
            $auditedSourceUuids = $model::where('is_audit', true)
                ->whereNotNull('source_uuid')
                ->pluck('source_uuid')
                ->toArray();
            $query = $model::with($this->auditRelations())
                ->where(function ($q) use ($auditedSourceUuids) {
                    $q->where('is_audit', true)
                        ->orWhere(function ($subQ) use ($auditedSourceUuids) {
                            $subQ->where('is_audit', false);

                            if (!empty($auditedSourceUuids)) {
                                $subQ->whereNotIn('uuid', $auditedSourceUuids);
                            }
                        });
                })
                ->when($user->role !== 'superadmin', function ($q) use ($user) {
                    $q->where($this->auditPlanColumn(), $user->id_plan);
                });
            if ($search) {
                $query->where(function ($q) use ($search) {
                    foreach ($this->auditSearchColumns() as $column) {
                        $q->orWhere($column, 'LIKE', '%' . $search . '%');
                    }
                });
            }
            $records = $query->orderBy($this->auditDateColumn(), 'desc')
                ->orderBy($this->auditTimeColumn(), 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
            return response()->json([
                'html' => view("{$this->auditViewPrefix()}._table", compact('records'))->render(),
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => (int) ceil($records->total() / $perPage),
                    'per_page' => $perPage,
                    'total' => $records->total(),
                    'from' => $records->firstItem(),
                    'to' => $records->lastItem(),
                    'has_pages' => $records->total() > $perPage,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }
}