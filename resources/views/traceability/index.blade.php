@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="mb-4">
                    <i class="fas fa-search-location mr-1"></i> Traceability
                </h5>

                <form method="GET" action="{{ route('traceability.search') }}"
                    class="report-sort-bar d-flex align-items-center flex-wrap mb-0">

                    <span class="report-sort-bar__label">
                        <i class="bi bi-search"></i> Kata Kunci
                    </span>
                    <input type="text" name="production_code"
                        class="report-sort-bar__select @error('production_code') is-invalid @enderror" style="width: 220px;"
                        placeholder="Kode produksi, nama produk, dll"
                        value="{{ old('production_code', $production_code ?? '') }}">

                    <span class="report-sort-bar__divider"></span>

                    <span class="report-sort-bar__label">
                        <i class="bi bi-calendar3"></i> Tanggal
                    </span>
                    <input type="date" name="date" class="report-sort-bar__select" value="{{ old('date', $date ?? '') }}">

                    <button type="submit" class="report-sort-bar__dir" title="Cari">
                        <i class="bi bi-search"></i>
                    </button>

                    @if (($production_code ?? null) || ($date ?? null))
                        <a href="{{ route('traceability.index') }}" class="report-sort-bar__dir" title="Reset filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </form>

                @error('production_code')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="card-body" style="padding-top: 1rem !important;">
                @if (!isset($results))
                    <div class="trace-empty">
                        <i class="fas fa-search"></i>
                        <p>
                            Masukkan kata kunci (kode produksi, nama produk) dan/atau pilih tanggal,
                            lalu klik <strong>Cari</strong> untuk menelusuri riwayat verifikasi.
                        </p>
                    </div>
                @else
                    @php
                        // Ratakan seluruh form dari semua modul jadi satu list, tetap bawa label modul asal
                        $flatResults = collect($results)->flatMap(function ($group) {
                            return collect($group['forms'])->map(function ($form) use ($group) {
                                return array_merge($form, [
                                    'module' => $group['module'],
                                    'module_label' => $group['label'],
                                ]);
                            });
                        })->sortByDesc('date')->values();

                        // Pagination manual karena data sudah full-loaded (bukan query builder)
                        $perPage = 20;
                        $page = (int) request('page', 1);
                        $paginatedResults = new \Illuminate\Pagination\LengthAwarePaginator(
                            $flatResults->forPage($page, $perPage)->values(),
                            $flatResults->count(),
                            $perPage,
                            $page,
                            ['path' => request()->url(), 'query' => request()->query()]
                        );

                        $byModule = $flatResults->groupBy('module');
                    @endphp

                    @if ($flatResults->isEmpty())
                        <div class="trace-empty">
                            <i class="fas fa-folder-open"></i>
                            <p>Tidak ada hasil yang cocok dengan kata kunci/tanggal tersebut.</p>
                        </div>
                    @else
                        {{-- Ringkasan --}}
                        <div class="trace-summary mb-3">
                            <div class="trace-summary__count">
                                <strong>{{ $flatResults->count() }}</strong> hasil
                                @if ($flatResults->count() > $perPage)
                                    <span class="text-muted">· Halaman {{ $paginatedResults->currentPage() }}/{{ $paginatedResults->lastPage() }}</span>
                                @endif
                            </div>
                            <div class="trace-summary__chips">
                                @foreach ($byModule as $mod => $items)
                                    <span class="trace-chip" style="--h: {{ crc32($mod) % 360 }}">
                                        {{ $items->first()['module_label'] }}
                                        <b>{{ $items->count() }}</b>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Daftar hasil --}}
                        <div id="traceability-results" data-search="{{ $production_code ?? '' }}" data-date="{{ $date ?? '' }}">
                            @foreach ($paginatedResults as $i => $item)
                                <div class="trace-card" style="--h: {{ crc32($item['module']) % 360 }}">
                                    <div class="trace-toggle" data-toggle="collapse" data-target="#trace-detail-{{ $i }}"
                                        aria-expanded="false" role="button" tabindex="0"
                                        data-module="{{ $item['module'] }}" data-form-key="{{ $item['form_key'] }}">

                                        <div class="trace-head">
                                            <div class="trace-head__main">
                                                <div class="trace-head__module">{{ $item['module_label'] }}</div>
                                                <div class="trace-head__meta">
                                                    <span>
                                                        <i class="far fa-calendar-alt"></i>
                                                        {{ \Carbon\Carbon::parse($item['date'])->translatedFormat('d M Y') }}
                                                    </span>
                                                    @if ($item['shift'])
                                                        <span class="trace-pill">Shift {{ $item['shift'] }}</span>
                                                    @endif
                                                    <span class="trace-pill trace-pill--match">{{ $item['match_count'] }} cocok</span>
                                                </div>
                                            </div>

                                            <div class="trace-head__actions">
                                                @if ($item['pdf_url'])
                                                    <a href="{{ $item['pdf_url'] }}" target="_blank" class="trace-pdf"
                                                        onclick="event.stopPropagation()">
                                                        <i class="fas fa-file-pdf"></i> PDF
                                                    </a>
                                                @endif
                                                <i class="fas fa-chevron-down trace-arrow"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="trace-detail-{{ $i }}" class="collapse">
                                        <div class="trace-detail-body">
                                            <div class="trace-skeleton">
                                                <span></span><span></span><span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($flatResults->count() > $perPage)
                            <div class="d-flex justify-content-end mt-3">
                                {{ $paginatedResults->links('pagination::bootstrap-4') }}
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            const $container = $('#traceability-results');
            if ($container.length === 0) return;

            const search = $container.data('search') || '';
            const date = $container.data('date') || '';
            const cache = {}; // key: module::form_key
            const detailUrl = '{{ route('traceability.formDetails') }}';

            const skeletonHtml = '<div class="trace-skeleton"><span></span><span></span><span></span></div>';

            $container.on('show.bs.collapse', '.collapse', function () {
                const $panel = $(this);
                const $header = $panel.prev('.trace-toggle');
                const $body = $panel.find('.trace-detail-body');
                const module = $header.data('module');
                const formKey = $header.data('form-key');
                const key = `${module}::${formKey}`;

                if (cache[key]) {
                    $body.html(cache[key]);
                    highlight($body, search);
                    return;
                }

                $body.html(skeletonHtml);

                $.ajax({
                    url: detailUrl,
                    method: 'GET',
                    data: {
                        module,
                        form_key: formKey,
                        search,
                        date
                    },
                    dataType: 'json',
                }).done(function (data) {
                    const html = data.mode === 'html'
                        ? data.html
                        : data.mode === 'table'
                            ? renderTable(data)
                            : renderDetails(data.details);
                    cache[key] = html;
                    $body.html(html);
                    highlight($body, search);
                }).fail(function (xhr) {
                    const msg = xhr.responseJSON?.message || xhr.statusText;
                    console.error(xhr.status, xhr.responseText);
                    $body.html(
                        '<div class="trace-error"><i class="fas fa-exclamation-triangle"></i> Gagal memuat detail.' +
                        '<br><code>' + escapeHtml(msg) + '</code></div>'
                    );
                });
            });

            // Enter / Spasi membuka kartu
            $container.on('keydown', '.trace-toggle', function (e) {
                if (e.target !== this) return;
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $($(this).data('target')).collapse('toggle');
                }
            });

            function highlight($root, term) {
                const tokens = (term || '').split(/\s+/).filter(t => t.length > 1);
                if (!tokens.length) return;

                const src = '(' + tokens.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|') + ')';
                const reTest = new RegExp(src, 'i');
                const reAll = new RegExp(src, 'gi');

                const walker = document.createTreeWalker($root[0], NodeFilter.SHOW_TEXT);
                const nodes = [];
                while (walker.nextNode()) nodes.push(walker.currentNode);

                nodes.forEach(n => {
                    if (!reTest.test(n.nodeValue)) return;
                    const wrap = document.createElement('span');
                    wrap.innerHTML = escapeHtml(n.nodeValue).replace(reAll, '<mark class="trace-hit">$1</mark>');
                    n.replaceWith(...wrap.childNodes);
                });
            }

            function renderTable(data) {
                const head = (data.columns || [])
                    .map(c => `<th>${escapeHtml(c)}</th>`).join('');

                const body = (data.rows || []).length
                    ? data.rows.map(row => `
                        <tr>${row.map(v => `<td>${escapeHtml(v ?? '-')}</td>`).join('')}</tr>
                    `).join('')
                    : `<tr><td colspan="${data.columns.length}" class="text-center">Tidak ada detail</td></tr>`;

                const notes = data.notes
                    ? `<p class="mt-2 mb-0 small text-muted">Catatan: ${escapeHtml(data.notes)}</p>` : '';

                return `
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead><tr>${head}</tr></thead>
                            <tbody>${body}</tbody>
                        </table>
                        ${notes}
                    </div>`;
            }

            function renderDetails(details) {
                if (!details || details.length === 0) {
                    return '<div class="text-muted small">Tidak ada detail.</div>';
                }

                return details.map(detail => {
                    const fieldsHtml = Object.entries(detail.fields || {}).map(([label, value]) => `
                        <div class="trace-field-row">
                            <span class="label">${escapeHtml(label)}</span>
                            <span class="value">${escapeHtml(value ?? '-')}</span>
                        </div>
                    `).join('');

                    const relatedHtml = (detail.related || []).map(rel => {
                        const relFields = Object.entries(rel.fields || {}).map(([label, value]) => `
                            <div class="trace-field-row">
                                <span class="label">${escapeHtml(label)}</span>
                                <span class="value">${escapeHtml(value ?? '-')}</span>
                            </div>
                        `).join('');
                        return `
                            <div class="trace-related-block">
                                <div class="related-label">${escapeHtml(rel.label)}</div>
                                ${relFields}
                            </div>
                        `;
                    }).join('');

                    return `
                        <div class="mb-3 pb-3 border-bottom">
                            ${fieldsHtml}
                            ${relatedHtml}
                        </div>
                    `;
                }).join('');
            }

            function escapeHtml(str) {
                return $('<div>').text(String(str ?? '')).html();
            }
        });
    </script>

    <style>
        /* ===== Filter bar (samakan dengan x-report-sort) ===== */
        .report-sort-bar {
            padding: .375rem .5rem .375rem .875rem;
            background: #f8f9fb;
            border: 1px solid #e6e8ec;
            border-radius: .75rem;
            width: fit-content;
            gap: .5rem;
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

        .report-sort-bar__select.is-invalid {
            border-color: #dc3545;
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

        /* ===== Empty state ===== */
        .trace-empty {
            text-align: center;
            padding: 3.5rem 1rem;
            color: #9ca3af;
        }

        .trace-empty i {
            font-size: 2.25rem;
            margin-bottom: 1rem;
            display: block;
            opacity: .6;
        }

        .trace-empty p {
            max-width: 460px;
            margin: 0 auto;
            font-size: .9rem;
            line-height: 1.6;
        }

        /* ===== Ringkasan ===== */
        .trace-summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .75rem 1rem;
        }

        .trace-summary__count {
            font-size: .9rem;
            color: #374151;
        }

        .trace-summary__chips {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
        }

        .trace-chip {
            font-size: .75rem;
            padding: .2rem .6rem;
            border-radius: 999px;
            background: hsl(var(--h) 80% 95%);
            color: hsl(var(--h) 55% 32%);
            border: 1px solid hsl(var(--h) 60% 85%);
        }

        .trace-chip b {
            margin-left: .25rem;
        }

        /* ===== Kartu hasil ===== */
        .trace-card {
            background: #fff;
            border: 1px solid #e6e8ec;
            border-left: 4px solid hsl(var(--h) 60% 52%);
            border-radius: .75rem;
            margin-bottom: .6rem;
            overflow: hidden;
            transition: box-shadow .15s ease;
        }

        .trace-card:hover {
            box-shadow: 0 4px 14px rgba(17, 24, 39, .07);
        }

        .trace-toggle {
            cursor: pointer;
            padding: .9rem 1.1rem;
            outline: none;
        }

        .trace-toggle:focus-visible {
            box-shadow: inset 0 0 0 2px #c7d2fe;
        }

        .trace-toggle::after {
            display: none !important;
            content: none !important;
        }

        .trace-toggle[aria-expanded="true"] {
            background: #fafbfd;
            border-bottom: 1px solid #eef0f3;
        }

        .trace-toggle[aria-expanded="true"] .trace-arrow {
            transform: rotate(180deg);
        }

        .trace-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .trace-head__module {
            font-weight: 600;
            font-size: .95rem;
            color: hsl(var(--h) 45% 28%);
        }

        .trace-head__meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem;
            margin-top: .25rem;
            font-size: .8rem;
            color: #6b7280;
        }

        .trace-head__meta .fa-calendar-alt {
            margin-right: .25rem;
            opacity: .7;
        }

        .trace-pill {
            padding: .1rem .5rem;
            border-radius: 999px;
            background: #f3f4f6;
            color: #4b5563;
            font-size: .72rem;
        }

        .trace-pill--match {
            background: #ecfdf5;
            color: #047857;
        }

        .trace-head__actions {
            display: flex;
            align-items: center;
            gap: .75rem;
            flex-shrink: 0;
        }

        .trace-pdf {
            font-size: .78rem;
            padding: .3rem .65rem;
            border-radius: .5rem;
            border: 1px solid #fecaca;
            color: #b91c1c;
            background: #fff5f5;
            text-decoration: none;
            transition: background .15s ease;
        }

        .trace-pdf:hover {
            background: #fee2e2;
            color: #991b1b;
            text-decoration: none;
        }

        .trace-arrow {
            color: #9ca3af;
            transition: transform .2s ease;
        }

        /* ===== Isi detail ===== */
        .trace-detail-body {
            padding: 1rem 1.1rem 1.1rem;
            background: #fff;
        }

        .trace-detail-body .table {
            font-size: .8rem;
        }

        .trace-detail-body .table th {
            background: #f3f4f8;
            color: #4b5563;
            font-weight: 600;
            vertical-align: middle;
        }

        .trace-detail-body .table td {
            vertical-align: middle;
        }

        .trace-detail-body h6 {
            font-size: .85rem;
            color: #374151;
        }

        /* Highlight kata kunci */
        mark.trace-hit {
            background: #fef08a;
            color: inherit;
            padding: 0 .1em;
            border-radius: .2rem;
        }

        /* Skeleton loading */
        .trace-skeleton {
            display: flex;
            flex-direction: column;
            gap: .6rem;
        }

        .trace-skeleton span {
            display: block;
            height: .9rem;
            border-radius: .4rem;
            background: linear-gradient(90deg, #eef0f3 25%, #f8f9fb 50%, #eef0f3 75%);
            background-size: 200% 100%;
            animation: trace-shimmer 1.2s infinite linear;
        }

        .trace-skeleton span:nth-child(1) { width: 100%; }
        .trace-skeleton span:nth-child(2) { width: 85%; }
        .trace-skeleton span:nth-child(3) { width: 60%; }

        @keyframes trace-shimmer {
            from { background-position: 200% 0; }
            to { background-position: -200% 0; }
        }

        /* Error */
        .trace-error {
            color: #b91c1c;
            font-size: .85rem;
        }

        .trace-error code {
            display: inline-block;
            margin-top: .25rem;
            color: #991b1b;
            word-break: break-word;
        }

        /* ===== Mode field list (modul lama) ===== */
        .trace-field-row {
            padding: .35rem 0;
            border-bottom: 1px solid #eaecf4;
            font-size: .875rem;
        }

        .trace-field-row:last-child {
            border-bottom: none;
        }

        .trace-field-row .label {
            color: #858796;
        }

        .trace-field-row .value {
            font-weight: 600;
            margin-left: .35rem;
        }

        .trace-related-block {
            background: #f8f9fc;
            border-radius: .35rem;
            padding: .75rem;
            margin-top: .5rem;
        }

        .trace-related-block .related-label {
            font-size: .7rem;
            text-transform: uppercase;
            color: #858796;
            letter-spacing: .05em;
            margin-bottom: .25rem;
        }

        @media (max-width: 576px) {
            .trace-head { flex-direction: column; align-items: flex-start; }
            .trace-head__actions { align-self: flex-end; }
        }
    </style>
@endsection