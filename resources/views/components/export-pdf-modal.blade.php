{{-- resources/views/components/export-pdf-modal.blade.php --}}
{{-- 
    Cara pakai:
    <x-export-pdf-modal :route="route('report_rm_arrivals.export_pdf_bulk')" title="RM Arrival" />
    <x-export-pdf-modal :route="route('report_thawings.export_pdf_bulk')" title="Pemeriksaan Thawing" />
--}}

@props([
    'route',
    'title'    => 'Export PDF',
    'modalId'  => 'modalExportPdf',
    'btnLabel' => 'Export PDF',
])

{{-- ── Tombol trigger ──────────────────────────────────────────────────────── --}}
<button type="button" class="btn btn-danger btn-sm"
    data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" title="Export PDF">
    <i class="fas fa-file-pdf me-1"></i> {{ $btnLabel }}
</button>

{{-- ── Modal ────────────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1"
    aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header text-white"
                style="background: linear-gradient(135deg, #CC7064, #D68B72);">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="fas fa-file-pdf me-2"></i> Export PDF — {{ $title }}
                </h5>
                <button type="button" class="btn-close btn-close-white"
                    data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ $route }}" method="GET"
                id="formExportPdf_{{ $modalId }}"
                data-modal-id="{{ $modalId }}" target="_blank">

                <div class="modal-body px-4 py-3">

                    {{-- Tipe filter --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Tipe Filter</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input export-pdf-filter-type"
                                    type="radio" name="export_type"
                                    id="pdf_opt_range_{{ $modalId }}" value="range" checked>
                                <label class="form-check-label mr-3" for="pdf_opt_range_{{ $modalId }}">
                                    Range Tanggal
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-pdf-filter-type"
                                    type="radio" name="export_type"
                                    id="pdf_opt_month_{{ $modalId }}" value="month">
                                <label class="form-check-label" for="pdf_opt_month_{{ $modalId }}">
                                    Per Bulan
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Range Tanggal --}}
                    <div class="export-pdf-section-range_{{ $modalId }}">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-muted">
                                    Dari Tanggal
                                </label>
                                <input type="date" class="form-control form-control-sm"
                                    name="start_date"
                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-muted">
                                    Sampai Tanggal
                                </label>
                                <input type="date" class="form-control form-control-sm"
                                    name="end_date"
                                    value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Per Bulan --}}
                    <div class="export-pdf-section-month_{{ $modalId }} d-none">
                        <label class="form-label small fw-semibold text-muted">
                            Pilih Bulan
                        </label>
                        <input type="month" class="form-control form-control-sm"
                            id="pdf_period_{{ $modalId }}"
                            value="{{ now()->format('Y-m') }}">
                        <input type="hidden" name="month" id="pdf_month_{{ $modalId }}">
                        <input type="hidden" name="year" id="pdf_year_{{ $modalId }}">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit"
                        class="btn btn-danger btn-sm export-pdf-submit-btn"
                        id="btnExportPdf_{{ $modalId }}">
                        <i class="fas fa-file-pdf me-1"></i> Download PDF
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalId = '{{ $modalId }}';
    const form = document.getElementById('formExportPdf_' + modalId);
    if (!form) return;

    const radios      = form.querySelectorAll('.export-pdf-filter-type');
    const rangeSection = form.querySelector('.export-pdf-section-range_' + modalId);
    const monthSection = form.querySelector('.export-pdf-section-month_' + modalId);

    const rangeInputs = rangeSection.querySelectorAll('input');
    const periodInput = document.getElementById('pdf_period_' + modalId);
    const monthHidden  = document.getElementById('pdf_month_' + modalId);
    const yearHidden   = document.getElementById('pdf_year_' + modalId);

    function toggleFields() {
        const checked = form.querySelector('.export-pdf-filter-type:checked');
        const isRange = checked ? checked.value === 'range' : true;

        rangeSection.classList.toggle('d-none', !isRange);
        monthSection.classList.toggle('d-none', isRange);

        rangeInputs.forEach(el => el.disabled = !isRange);
        periodInput.disabled = isRange;
    }

    radios.forEach(r => r.addEventListener('change', toggleFields));
    toggleFields(); // set initial state

    form.addEventListener('submit', function () {
        // Split value "YYYY-MM" dari input type=month jadi 2 hidden field
        if (periodInput.value) {
            const [y, m] = periodInput.value.split('-');
            yearHidden.value  = y;
            monthHidden.value = parseInt(m, 10);
        }
    });
});
</script>