@props([
    'prefix', 'title', 'color', 'icon',
    'actionRoute', 'countRoute', 'label',
])

@php
    $modalId      = 'modalBulk' . ucfirst($prefix);
    $monthFieldId = $prefix . 'MonthField';
    $rangeFieldId = $prefix . 'RangeField';
    $monthInputId = $prefix . '_month';
    $dateFromId   = $prefix . '_date_from';
    $dateToId     = $prefix . '_date_to';
    $countPreview = $prefix . 'CountPreview';
    $countLoading = $prefix . 'CountLoading';
    $countNumber  = $prefix . 'CountNumber';
    $byMonthId    = $prefix . 'ByMonth';
    $byRangeId    = $prefix . 'ByRange';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route($actionRoute) }}" method="POST">
                @csrf

                <div class="modal-header bg-{{ $color }} {{ $color === 'success' ? 'text-white' : '' }}">
                    <h5 class="modal-title">
                        <i class="fas {{ $icon }} me-1"></i> Approval Form — {{ $title }}
                    </h5>
                    <button type="button"
                            class="btn-close {{ $color === 'success' ? 'btn-close-white' : '' }}"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    {{-- Filter Type --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Filter Periode</label>
                        <div class="d-flex gap-3" style="gap: 1rem;">
                            <div class="form-check">
                                <input class="form-check-input bulk-filter-type"
                                       type="radio" name="filter_type"
                                       id="{{ $byMonthId }}" value="month"
                                       data-prefix="{{ $prefix }}" checked>
                                <label class="form-check-label" for="{{ $byMonthId }}">Per Bulan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input bulk-filter-type"
                                       type="radio" name="filter_type"
                                       id="{{ $byRangeId }}" value="range"
                                       data-prefix="{{ $prefix }}">
                                <label class="form-check-label" for="{{ $byRangeId }}">Range Tanggal</label>
                            </div>
                        </div>
                    </div>

                    {{-- Per Bulan --}}
                    <div id="{{ $monthFieldId }}">
                        <div class="mb-3">
                            <label for="{{ $monthInputId }}" class="form-label">Pilih Bulan</label>
                            <input type="month" class="form-control bulk-input"
                                   name="month" id="{{ $monthInputId }}"
                                   data-prefix="{{ $prefix }}"
                                   value="{{ now()->format('Y-m') }}">
                        </div>
                    </div>

                    {{-- Range Tanggal --}}
                    <div id="{{ $rangeFieldId }}" style="display:none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="{{ $dateFromId }}" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control bulk-input"
                                       name="date_from" id="{{ $dateFromId }}"
                                       data-prefix="{{ $prefix }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="{{ $dateToId }}" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control bulk-input"
                                       name="date_to" id="{{ $dateToId }}"
                                       data-prefix="{{ $prefix }}">
                            </div>
                        </div>
                    </div>

                    {{-- Count Preview --}}
                    <div id="{{ $countPreview }}" class="alert alert-secondary py-2 small mb-2" style="display:none;">
                        <i class="fas fa-file-alt me-1"></i>
                        Laporan yang akan diproses:
                        <span id="{{ $countLoading }}" style="display:none;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                        <strong id="{{ $countNumber }}" class="fs-6">0</strong> form
                    </div>

                    <div class="alert alert-{{ $color }} py-2 small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Hanya laporan yang <strong>belum diproses</strong> pada periode ini yang akan diproses.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-{{ $color }} btn-sm">
                        <i class="fas {{ $icon }} me-1"></i> {{ $label }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JS khusus modal ini --}}
<script>
(function () {
    const prefix      = @json($prefix);
    const countUrl    = @json(route($countRoute));
    const modalId     = 'modalBulk' + prefix.charAt(0).toUpperCase() + prefix.slice(1);

    function getParams() {
        const type = document.querySelector(`#${modalId} input[name="filter_type"]:checked`)?.value;
        if (type === 'month') {
            return { filter_type: 'month', month: document.getElementById(`${prefix}_month`).value };
        }
        return {
            filter_type: 'range',
            date_from: document.getElementById(`${prefix}_date_from`).value,
            date_to:   document.getElementById(`${prefix}_date_to`).value,
        };
    }

    function fetchCount() {
        const params     = getParams();
        const previewEl  = document.getElementById(`${prefix}CountPreview`);
        const loadingEl  = document.getElementById(`${prefix}CountLoading`);
        const numberEl   = document.getElementById(`${prefix}CountNumber`);

        previewEl.style.display  = '';
        loadingEl.style.display  = 'inline';
        numberEl.textContent     = '';

        fetch(`${countUrl}?${new URLSearchParams(params)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            loadingEl.style.display = 'none';
            numberEl.textContent    = data.count;
            previewEl.className     = `alert py-2 small mb-2 ${data.count > 0 ? 'alert-info' : 'alert-secondary'}`;
        })
        .catch(() => {
            loadingEl.style.display = 'none';
            numberEl.textContent    = '?';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Toggle month/range
        document.querySelectorAll(`#${modalId} input[name="filter_type"]`).forEach(r => {
            r.addEventListener('change', function () {
                document.getElementById(`${prefix}MonthField`).style.display = this.value === 'month' ? '' : 'none';
                document.getElementById(`${prefix}RangeField`).style.display = this.value === 'range' ? '' : 'none';
                fetchCount();
            });
        });

        // Input changes
        [`${prefix}_month`, `${prefix}_date_from`, `${prefix}_date_to`].forEach(id => {
            document.getElementById(id)?.addEventListener('change', fetchCount);
        });

        // Fetch saat modal dibuka
        document.getElementById(modalId)?.addEventListener('shown.bs.modal', fetchCount);
    });
})();
</script>