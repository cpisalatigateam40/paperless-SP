@props([
    'sortOptions' => [
        'latest' => 'Terbaru',
        'submitted_at' => 'Tanggal Submit',
    ],
    'withDateFilter' => false,
    'dateFilterName' => 'report_date',
    'dateFilterLabel' => 'Tanggal',
])

<form method="GET" class="report-sort-bar d-flex align-items-center flex-wrap gap-2 mb-3">
    @foreach(request()->except(['sort_by', 'sort_dir', $dateFilterName, 'page']) as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    @if($withDateFilter)
        <span class="report-sort-bar__label">
            <i class="bi bi-calendar3"></i> {{ $dateFilterLabel }}
        </span>
        <input
            type="date"
            name="{{ $dateFilterName }}"
            value="{{ request($dateFilterName) }}"
            class="report-sort-bar__select"
            onchange="this.form.submit()"
        >
        @if(request()->filled($dateFilterName))
            <a href="{{ request()->fullUrlWithQuery([$dateFilterName => null]) }}" class="report-sort-bar__dir" title="Hapus filter tanggal">
                <i class="bi bi-x-lg"></i>
            </a>
        @endif
        <span class="report-sort-bar__divider"></span>
    @endif

    <span class="report-sort-bar__label">
        <i class="bi bi-sort-down"></i> Urutkan
    </span>

    <select name="sort_by" class="report-sort-bar__select" onchange="this.form.submit()">
        @foreach($sortOptions as $value => $label)
            <option value="{{ $value }}" {{ request('sort_by', 'latest') == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>

    @php $dir = request('sort_dir', 'desc'); @endphp
    <input type="hidden" name="sort_dir" value="{{ $dir }}">

    <button type="submit"
            class="report-sort-bar__dir"
            title="{{ $dir === 'asc' ? 'A–Z / Terlama dulu' : 'Z–A / Terbaru dulu' }}"
            onclick="this.form.sort_dir.value = '{{ $dir === 'asc' ? 'desc' : 'asc' }}'">
        <i class="bi {{ $dir === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-down-alt' }}"></i>
    </button>
</form>

<style>
.report-sort-bar {
    padding: .375rem .5rem .375rem .875rem;
    background: #f8f9fb;
    border: 1px solid #e6e8ec;
    border-radius: .75rem;
    width: fit-content;
}

.report-sort-bar__label {
    font-size: .8125rem;
    font-weight: 600;
    color: #6b7280;
    display: inline-flex;
    align-items: center;
    gap: .375rem;
    white-space: nowrap;
}

.report-sort-bar__divider {
    width: 1px;
    height: 1.25rem;
    background: #dde1e6;
    margin: 0 .125rem;
}

.report-sort-bar__select {
    appearance: none;
    border: 1px solid #dde1e6;
    background: #fff;
    border-radius: .5rem;
    padding: .375rem .75rem;
    font-size: .8125rem;
    color: #1f2937;
    transition: border-color .15s ease;
}

.report-sort-bar__select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
}

.report-sort-bar__dir {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.125rem;
    height: 2.125rem;
    border: 1px solid #dde1e6;
    background: #fff;
    border-radius: .5rem;
    color: #4b5563;
    text-decoration: none;
    transition: background .15s ease, color .15s ease, border-color .15s ease;
}

.report-sort-bar__dir:hover {
    background: #eef2ff;
    border-color: #c7d2fe;
    color: #4338ca;
}
</style>