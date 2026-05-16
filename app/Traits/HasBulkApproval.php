<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait HasBulkApproval
{
    /**
     * Override di controller masing-masing:
     * protected string $bulkModel = ReportRmArrival::class;
     * protected string $dateColumn = 'date'; // default 'date'
     */

    protected string $dateColumn = 'date';

    // =========================================================
    // PRIVATE HELPER
    // =========================================================

    private function buildBulkQuery(string $nullColumn, Request $request)
    {
        $model = $this->bulkModel;
        $query = $model::whereNull($nullColumn);

        if ($request->filter_type === 'month' && $request->month) {
            $query->whereYear($this->dateColumn, substr($request->month, 0, 4))
                  ->whereMonth($this->dateColumn, substr($request->month, 5, 2));
        } elseif ($request->filter_type === 'range' && $request->date_from && $request->date_to) {
            $query->whereBetween($this->dateColumn, [$request->date_from, $request->date_to]);
        }

        return $query;
    }

    private function validateBulkRequest(Request $request): void
    {
        $request->validate([
            'filter_type' => 'required|in:month,range',
            'month'       => 'required_if:filter_type,month|nullable|date_format:Y-m',
            'date_from'   => 'required_if:filter_type,range|nullable|date',
            'date_to'     => 'required_if:filter_type,range|nullable|date|after_or_equal:date_from',
        ]);
    }

    private function getBulkCount(string $nullColumn, Request $request): int
    {
        if (
            ($request->filter_type === 'month' && !$request->month) ||
            ($request->filter_type === 'range' && (!$request->date_from || !$request->date_to))
        ) {
            return 0;
        }

        return $this->buildBulkQuery($nullColumn, $request)->count();
    }

    // =========================================================
    // KNOWN (Produksi)
    // =========================================================

    public function bulkKnown(Request $request)
    {
        $this->validateBulkRequest($request);

        $query = $this->buildBulkQuery('known_by', $request);
        $count = $query->count();

        if ($count === 0) {
            return redirect()->back()->with('error', 'Tidak ada laporan yang belum diketahui pada periode tersebut.');
        }

        $query->update(['known_by' => Auth::user()->name]);

        return redirect()->back()->with('success', "Berhasil mengetahui {$count} laporan.");
    }

    public function bulkKnownCount(Request $request)
    {
        return response()->json(['count' => $this->getBulkCount('known_by', $request)]);
    }

    // =========================================================
    // APPROVE (QC)
    // =========================================================

    public function bulkApprove(Request $request)
    {
        $this->validateBulkRequest($request);

        $query = $this->buildBulkQuery('approved_by', $request);
        $count = $query->count();

        if ($count === 0) {
            return redirect()->back()->with('error', 'Tidak ada laporan yang belum disetujui pada periode tersebut.');
        }

        $query->update([
            'approved_by' => Auth::user()->name,
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', "Berhasil menyetujui {$count} laporan.");
    }

    public function bulkApproveCount(Request $request)
    {
        return response()->json(['count' => $this->getBulkCount('approved_by', $request)]);
    }
}