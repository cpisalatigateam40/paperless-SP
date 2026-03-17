{{-- resources/views/components/export-excel-modal.blade.php --}}
{{-- 
    Cara pakai:
    <x-export-excel-modal :route="route('report_thawings.export')" title="Pemeriksaan Thawing" />
    <x-export-excel-modal :route="route('report-premixes.export')" title="Premix" />
    <x-export-excel-modal :route="route('report_rm_arrivals.export')" title="RM Arrival" />
--}}

@props([
    'route',
    'title'    => 'Export Excel',
    'modalId'  => 'modalExport',
    'btnLabel' => 'Export Excel',
])

{{-- ── Tombol trigger ──────────────────────────────────────────────────────── --}}
<button type="button" class="btn btn-success btn-sm"
    data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" title="Export Excel">
    <i class="fas fa-file-excel me-1"></i> {{ $btnLabel }}
</button>

{{-- ── Modal ────────────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1"
    aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header text-white"
                style="background: linear-gradient(135deg, #CC7064, #D68B72);">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="fas fa-file-excel me-2"></i> Export Excel — {{ $title }}
                </h5>
                <button type="button" class="btn-close btn-close-white"
                    data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ $route }}" method="POST"
                id="formExport_{{ $modalId }}"
                data-modal-id="{{ $modalId }}">
                @csrf

                <div class="modal-body px-4 py-3">

                    {{-- Tipe filter --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Tipe Filter</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input export-filter-type"
                                    type="radio" name="filter_type"
                                    id="opt_range_{{ $modalId }}" value="range" checked>
                                <label class="form-check-label mr-3" for="opt_range_{{ $modalId }}">
                                    Range Tanggal
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-filter-type"
                                    type="radio" name="filter_type"
                                    id="opt_month_{{ $modalId }}" value="month">
                                <label class="form-check-label" for="opt_month_{{ $modalId }}">
                                    Per Bulan
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Range Tanggal --}}
                    <div class="export-section-range_{{ $modalId }}">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-muted">
                                    Dari Tanggal
                                </label>
                                <input type="date" class="form-control form-control-sm"
                                    name="date_from"
                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-muted">
                                    Sampai Tanggal
                                </label>
                                <input type="date" class="form-control form-control-sm"
                                    name="date_to"
                                    value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Per Bulan --}}
                    <div class="export-section-month_{{ $modalId }} d-none">
                        <label class="form-label small fw-semibold text-muted">
                            Pilih Bulan
                        </label>
                        <input type="month" class="form-control form-control-sm"
                            name="month" value="{{ now()->format('Y-m') }}">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit"
                        class="btn btn-success btn-sm export-submit-btn"
                        id="btnExport_{{ $modalId }}">
                        <i class="fas fa-file-excel me-1"></i> Download Excel
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>