<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait HasAuditController
{
    abstract protected function auditModel(): string;
    abstract protected function auditRoutePrefix(): string;
    abstract protected function auditViewPrefix(): string;
    abstract protected function auditRelations(): array;
    abstract protected function auditSearchColumns(): array;
    abstract protected function auditDateColumn(): string;
    abstract protected function auditTimeColumn(): string;
    abstract protected function auditPlanColumn(): ?string;

    /**
     * Smart Copy & Edit Audit: auto copy jika belum ada versi audit
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
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 10);

        $records = $this->buildAuditQuery($search)
            ->paginate($perPage)
            ->withQueryString();

        return view("{$this->auditViewPrefix()}.index_audit", compact('records', 'search', 'perPage'));
    }

    /**
     * AJAX Search untuk Halaman Audit
     * (butuh view {prefix}._table; tidak dipakai kalau halaman audit non-AJAX)
     */
    public function auditSearchAjax(Request $request)
    {
        try {
            $search = $request->get('search');
            $perPage = (int) $request->get('per_page', 10);
            $page = (int) $request->get('page', 1);

            $records = $this->buildAuditQuery($search)
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'html' => view("{$this->auditViewPrefix()}._table", compact('records'))->render(),
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => (int) ceil($records->total() / max($perPage, 1)),
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

    /**
     * Query gabungan: semua baris audit + baris operasional yang belum punya salinan
     */
    protected function buildAuditQuery(?string $search)
    {
        $model = $this->auditModel();
        $user = Auth::user();
        $planColumn = $this->auditPlanColumn();

        // source_uuid yang sudah punya salinan audit
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
            ->when($user->role !== 'superadmin' && $planColumn, function ($q) use ($user, $planColumn) {
                $q->where($planColumn, $user->id_plan);
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                foreach ($this->auditSearchColumns() as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $search . '%');
                }
            });
        }

        return $query
            ->orderBy($this->auditDateColumn(), 'desc')
            ->orderBy($this->auditTimeColumn(), 'desc');
    }

    public function autoNormalizeAudit($uuid)
    {
        $model = $this->auditModel();
        $item = $model::where('uuid', $uuid)->firstOrFail();

        abort_unless($item->hasAuditNormalizeRules(), 404);

        $audit = $item->is_audit
            ? $item
            : ($item->auditVersion ?: $item->copyToAudit());

        // data audit yang sudah di-approve tidak diubah lagi
        if ($audit->approved_by) {
            return redirect()->route("{$this->auditRoutePrefix()}.audit")
                ->with('error', 'Data audit sudah di-approve, tidak bisa diubah otomatis.');
        }

        $changed = \Illuminate\Support\Facades\DB::transaction(fn () => $audit->normalizeAudit());

        return redirect()->route("{$this->auditRoutePrefix()}.audit")
            ->with('success', $changed > 0
                ? "Data audit diperbarui otomatis ({$changed} baris diubah menjadi OK)."
                : 'Tidak ada ketidaksesuaian yang perlu diubah.');
    }
}