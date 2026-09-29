@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="mb-4">
                    <i class="fas fa-search-location mr-1"></i> Traceability
                </h5>

                <form method="GET" action="{{ route('traceability.search') }}"
                    class="report-sort-bar d-flex align-items-center flex-wrap gap-2 mb-0">

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
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-search fa-2x mb-3 d-block"></i>
                        Masukkan kata kunci (kode produksi, nama produk) dan/atau pilih tanggal,
                        lalu klik <strong>Cari</strong> untuk menelusuri riwayat verifikasi.
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
                    @endphp

                    @if ($flatResults->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-folder-open fa-2x mb-3 d-block"></i>
                            Tidak ada hasil yang cocok dengan kata kunci/tanggal tersebut.
                        </div>
                    @else
                        <div class="mb-2 text-muted small">
                            Ditemukan {{ $flatResults->count() }} hasil
                            @if ($flatResults->count() > $perPage)
                                · Halaman {{ $paginatedResults->currentPage() }} dari {{ $paginatedResults->lastPage() }}
                            @endif
                        </div>

                        <div id="traceability-results" data-search="{{ $production_code ?? '' }}" data-date="{{ $date ?? '' }}">
                            @foreach ($paginatedResults as $i => $item)
                                <div class="card shadow-sm mb-2 trace-card">
                                    <div class="card-header bg-white py-4 px-4 trace-toggle" data-toggle="collapse"
                                        data-target="#trace-detail-{{ $i }}" aria-expanded="false" role="button"
                                        data-module="{{ $item['module'] }}" data-form-key="{{ $item['form_key'] }}"
                                        style="margin: unset !important;">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                                            <div>
                                                <span class="badge badge-primary mr-2">{{ $item['module_label'] }}</span>
                                                <span class="font-weight-bold">{{ $item['date'] }}</span>
                                                @if ($item['shift'])
                                                    <span class="text-muted small ml-1">· Shift {{ $item['shift'] }}</span>
                                                @endif
                                                <span class="text-muted small ml-1">· {{ $item['match_count'] }} cocok</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                @if ($item['pdf_url'])
                                                    <a href="{{ $item['pdf_url'] }}" target="_blank"
                                                        class="btn btn-sm btn-outline-secondary mr-2" onclick="event.stopPropagation()">
                                                        <i class="fas fa-file-pdf"></i> PDF
                                                    </a>
                                                @endif
                                                <i class="fas fa-chevron-down trace-arrow text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="trace-detail-{{ $i }}" class="collapse">
                                        <div class="card-body border-top trace-detail-body">
                                            <div class="text-muted small">
                                                <i class="fas fa-spinner fa-spin mr-1"></i> Memuat...
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

            $container.on('show.bs.collapse', '.collapse', function () {
                const $panel = $(this);
                const $header = $panel.prev('.trace-toggle');
                const $body = $panel.find('.trace-detail-body');
                const module = $header.data('module');
                const formKey = $header.data('form-key');
                const key = `${module}::${formKey}`;

                if (cache[key]) {
                    $body.html(cache[key]);
                    return;
                }

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
                    const html = renderDetails(data.details);
                    cache[key] = html;
                    $body.html(html);
                }).fail(function () {
                    $body.html('<div class="text-danger small">Gagal memuat detail.</div>');
                });
            });

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
                return $('<div>').text(str).html();
            }
        });
    </script>

    <style>
        /* Filter bar (samakan dengan x-report-sort) */
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

        /* Kartu hasil */
        .trace-toggle {
            cursor: pointer;
        }

        .trace-toggle::after {
            display: none !important;
            content: none !important;
        }

        .trace-toggle[aria-expanded="true"] .trace-arrow {
            transform: rotate(180deg);
        }

        .trace-arrow {
            transition: transform .15s ease;
        }

        .trace-card:hover .card-header {
            background-color: #f8f9fc !important;
        }

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
    </style>
@endsection