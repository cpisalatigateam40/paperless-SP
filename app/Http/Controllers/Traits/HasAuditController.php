<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $changed = \Illuminate\Support\Facades\DB::transaction(fn () => $audit->normalizeAudit());

        return redirect()->route("{$this->auditRoutePrefix()}.edit", $audit->uuid)
            ->with('success', $changed > 0
                ? "Data audit diperbarui otomatis ({$changed} baris diubah menjadi OK). Silakan cek dan sesuaikan jika perlu."
                : 'Tidak ada ketidaksesuaian yang perlu diubah. Silakan cek data audit.');
    }

    public function bulkAutoNormalizeAudit(Request $request)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        // batasi rentang supaya request tidak terlalu berat
        if (\Carbon\Carbon::parse($data['start_date'])->diffInDays($data['end_date']) > 92) {
            return back()->withInput()->with('error', 'Rentang tanggal maksimal 92 hari.');
        }

        $model = $this->auditModel();
        abort_unless((new $model)->hasAuditNormalizeRules(), 404);

        set_time_limit(0);

        $user       = Auth::user();
        $planColumn = $this->auditPlanColumn();
        $dateColumn = $this->auditDateColumn();

        $base = $model::query()
            ->whereDate($dateColumn, '>=', $data['start_date'])
            ->whereDate($dateColumn, '<=', $data['end_date'])
            ->when($user->role !== 'superadmin' && $planColumn, fn ($q) => $q->where($planColumn, $user->id_plan));

        // 1) data audit yang sudah ada di rentang tanggal
        $auditUuids = (clone $base)->where('is_audit', true)->pluck('uuid');

        // 2) data operasional yang belum punya salinan audit
        //    (yang sudah punya salinan tercakup di poin 1 karena tanggalnya sama)
        $sourceUuids = (clone $base)->where('is_audit', false)
            ->whereDoesntHave('auditVersion')
            ->pluck('uuid');

        $checked = $changedReports = $changedRows = $failed = 0;

        foreach ($auditUuids as $uuid) {
            $record = $model::where('uuid', $uuid)->first();
            if (!$record) {
                continue;
            }

            $checked++;

            try {
                $n = DB::transaction(fn () => $record->normalizeAudit());

                if ($n > 0) {
                    $changedReports++;
                    $changedRows += $n;
                }
            } catch (\Throwable $e) {
                $failed++;
                report($e);
            }
        }

        foreach ($sourceUuids as $uuid) {
            $record = $model::where('uuid', $uuid)->first();
            if (!$record) {
                continue;
            }

            $checked++;

            // salin dulu, normalisasi, lalu rollback kalau tidak ada yang berubah
            // supaya tidak tercipta salinan audit yang tidak perlu
            DB::beginTransaction();

            try {
                $clone = $record->copyToAudit();
                $n     = $clone->normalizeAudit();

                if ($n > 0) {
                    DB::commit();
                    $changedReports++;
                    $changedRows += $n;
                } else {
                    DB::rollBack();
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                $failed++;
                report($e);
            }
        }

        $message = "Edit otomatis selesai: {$checked} laporan diperiksa, "
            . "{$changedReports} laporan diubah ({$changedRows} baris).";

        if ($failed > 0) {
            $message .= " {$failed} laporan gagal diproses, cek laravel.log.";
        }

        return redirect()->route("{$this->auditRoutePrefix()}.audit")
            ->with($failed > 0 ? 'error' : 'success', $message);
    }
}