@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="mb-4">
                    <i class="fas fa-search-location mr-1"></i> Traceability
                </h5>

                {{-- ===== Filter ===== --}}
                <form method="GET" action="{{ route('traceability.search') }}" class="trace-filter">
                    <div class="trace-filter__row">
                        <div class="trace-filter__field trace-filter__field--wide">
                            <label><i class="bi bi-search"></i> Kata Kunci</label>
                            <input type="text" name="production_code"
                                class="trace-filter__input @error('production_code') is-invalid @enderror"
                                placeholder="Kode produksi, nama produk"
                                value="{{ old('production_code', $production_code ?? '') }}">
                        </div>

                        <div class="trace-filter__field trace-filter__field--product">
                            <label><i class="bi bi-box-seam"></i> Produk</label>
                            <select name="product" class="trace-filter__input">
                                <option value="">Semua produk</option>
                                @foreach ($products ?? [] as $p)
                                    <option value="{{ $p }}" @selected(old('product', $filters['product'] ?? '') === $p)>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if (($areas ?? collect())->isNotEmpty())
                            <div class="trace-filter__field trace-filter__field--area">
                                <label><i class="bi bi-building"></i> Plant</label>
                                <select name="area" class="trace-filter__input">
                                    <option value="">Semua plant</option>
                                    @foreach ($areas as $a)
                                        <option value="{{ $a->uuid }}" @selected(old('area', $filters['area'] ?? '') === $a->uuid)>{{ $a->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="trace-filter__field trace-filter__field--shift">
                            <label><i class="bi bi-clock-history"></i> Shift</label>
                            <select name="shift" class="trace-filter__input">
                                <option value="">Semua</option>
                                @foreach ($shifts ?? [] as $s)
                                    <option value="{{ $s }}" @selected(old('shift', $filters['shift'] ?? '') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="trace-filter__field trace-filter__field--date">
                            <label><i class="bi bi-calendar3"></i> Dari</label>
                            <input type="date" name="date_from" class="trace-filter__input"
                                value="{{ old('date_from', $filters['date_from'] ?? '') }}">
                        </div>

                        <div class="trace-filter__field trace-filter__field--date">
                            <label><i class="bi bi-calendar3"></i> Sampai</label>
                            <input type="date" name="date_to" class="trace-filter__input"
                                value="{{ old('date_to', $filters['date_to'] ?? '') }}">
                        </div>

                        <div class="trace-filter__field trace-filter__field--preset">
                            <label><i class="bi bi-lightning-charge"></i> Cepat</label>
                            <select class="trace-filter__input" id="traceRangePreset">
                                <option value="">Pilih</option>
                                <option value="0">Hari ini</option>
                                <option value="7">7 hari</option>
                                <option value="30">30 hari</option>
                            </select>
                        </div>

                        <div class="trace-filter__buttons">
                            @if (($production_code ?? null) || !empty($filters))
                                <a href="{{ route('traceability.index') }}"
                                    class="trace-filter__btn trace-filter__btn--reset" title="Reset filter">
                                    <i class="bi bi-x-lg"></i><span class="trace-filter__btntext">Reset</span>
                                </a>
                            @endif
                            <button type="submit" class="trace-filter__btn trace-filter__btn--submit" title="Cari">
                                <i class="bi bi-search"></i><span class="trace-filter__btntext">Cari</span>
                            </button>
                        </div>
                    </div>
                </form>

                {{-- ===== Chip pencarian aktif ===== --}}
                @if (isset($results))
                    @php
                        $q = request()->query();
                        $chips = [];

                        if (filled($production_code ?? null)) {
                            $chips[] = ['icon' => 'bi-search', 'label' => 'Kata kunci', 'value' => $production_code, 'drop' => ['production_code']];
                        }
                        if (!empty($filters['product'])) {
                            $chips[] = ['icon' => 'bi-box-seam', 'label' => 'Produk', 'value' => $filters['product'], 'drop' => ['product']];
                        }
                        if (!empty($filters['area'])) {
                            $chips[] = ['icon' => 'bi-building', 'label' => 'Plant',
                                'value' => optional(($areas ?? collect())->firstWhere('uuid', $filters['area']))->name ?? '-',
                                'drop' => ['area']];
                        }
                        if (!empty($filters['shift'])) {
                            $chips[] = ['icon' => 'bi-clock-history', 'label' => 'Shift', 'value' => $filters['shift'], 'drop' => ['shift']];
                        }

                        $from = $filters['date_from'] ?? null;
                        $to = $filters['date_to'] ?? null;
                        if ($from || $to) {
                            $fmt = fn($d) => \Carbon\Carbon::parse($d)->translatedFormat('d M Y');
                            $range = $from && $to ? $fmt($from) . ' – ' . $fmt($to)
                                : ($from ? 'Sejak ' . $fmt($from) : 'Hingga ' . $fmt($to));
                            $chips[] = ['icon' => 'bi-calendar3', 'label' => 'Tanggal', 'value' => $range, 'drop' => ['date_from', 'date_to']];
                        }

                        $chipUrl = function (array $drop) use ($q) {
                            $rest = collect($q)->except(array_merge($drop, ['page']))->filter(fn($v) => filled($v));
                            return $rest->isEmpty() ? route('traceability.index') : route('traceability.search', $rest->all());
                        };
                    @endphp

                    @if (count($chips))
                        <div class="trace-active">
                            <span class="trace-active__title">Pencarian aktif:</span>

                            @foreach ($chips as $chip)
                                <span class="trace-active__chip">
                                    <i class="bi {{ $chip['icon'] }}"></i>
                                    <span class="trace-active__label">{{ $chip['label'] }}:</span>
                                    <strong>{{ $chip['value'] }}</strong>
                                    <a href="{{ $chipUrl($chip['drop']) }}" title="Hapus filter ini"
                                        aria-label="Hapus filter {{ $chip['label'] }}">
                                        <i class="bi bi-x"></i>
                                    </a>
                                </span>
                            @endforeach

                            @if (count($chips) > 1)
                                <a href="{{ route('traceability.index') }}" class="trace-active__clear">Hapus semua</a>
                            @endif
                        </div>
                    @endif
                @endif

                @error('production_code')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="card-body" style="padding-top: 1rem !important;">
                @if (!isset($results))
                    <div class="trace-empty">
                        <i class="fas fa-search"></i>
                        <p>
                            Masukkan kata kunci (kode produksi, nama produk) dan/atau pilih filter,
                            lalu klik <strong>Cari</strong> untuk menelusuri riwayat verifikasi.
                        </p>
                    </div>
                @else
                    @php
                        $flatResults = collect($results)->flatMap(function ($group) {
                            return collect($group['forms'])->map(function ($form) use ($group) {
                                return array_merge($form, [
                                    'module' => $group['module'],
                                    'module_label' => $group['label'],
                                ]);
                            });
                        })->sortByDesc('date')->values();

                        $byModule = $flatResults->groupBy('module');
                        $uid = 0;
                    @endphp

                    @if ($flatResults->isEmpty())
                        <div class="trace-empty">
                            <i class="fas fa-folder-open"></i>
                            <p>Tidak ada hasil yang cocok dengan kata kunci/filter tersebut.</p>
                        </div>
                    @else
                        <div class="trace-summary mb-3">
                            <div class="trace-summary__count">
                                <strong>{{ $flatResults->count() }}</strong> hasil di
                                <strong>{{ $byModule->count() }}</strong> modul
                            </div>
                        </div>

                        <div id="traceability-results" data-search="{{ $production_code ?? '' }}"
                            data-filters="{{ json_encode($filters ?? []) }}">

                            @foreach ($byModule as $mod => $items)
                                @php
                                    $gid = 'trace-group-' . $loop->index;
                                    $open = $byModule->count() === 1;
                                    $newest = \Carbon\Carbon::parse($items->first()['date'])->translatedFormat('d M Y');
                                    $oldest = \Carbon\Carbon::parse($items->last()['date'])->translatedFormat('d M Y');
                                @endphp

                                <div class="trace-group" style="--h: {{ crc32($mod) % 360 }}">
                                    {{-- Header grup modul --}}
                                    <div class="trace-group__toggle" data-toggle="collapse" data-target="#{{ $gid }}"
                                        aria-expanded="{{ $open ? 'true' : 'false' }}" role="button" tabindex="0">
                                        <div class="trace-head">
                                            <div class="trace-head__main">
                                                <div class="trace-head__module">{{ $items->first()['module_label'] }}</div>
                                                <div class="trace-head__meta">
                                                    <span class="trace-pill trace-pill--match">{{ $items->count() }} form</span>
                                                    <span>
                                                        <i class="far fa-calendar-alt"></i>
                                                        {{ $newest }}@if ($newest !== $oldest) – {{ $oldest }}@endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="trace-head__actions">
                                                <i class="fas fa-chevron-down trace-arrow"></i>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Isi grup: tabel + pager --}}
                                    <div id="{{ $gid }}" class="collapse {{ $open ? 'show' : '' }}">
                                        <div class="trace-group__body">
                                            <div class="table-responsive">
                                                <table class="table trace-table mb-0" data-per-page="10">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center" style="width:48px">No</th>
                                                            <th>Tanggal</th>
                                                            <th>Shift</th>
                                                            <th>Waktu</th>
                                                            <th>Area</th>
                                                            <th>Produk</th>
                                                            <th>Kode Produksi</th>
                                                            <th>Dibuat Oleh</th>
                                                            <th class="text-center" style="width:96px">Aksi</th>
                                                        </tr>
                                                    </thead>

                                                    @foreach ($items as $item)
                                                        @php
                                                            $uid++;
                                                            $date = \Carbon\Carbon::parse($item['date']);
                                                            $prodList = collect(explode(', ', $item['products'] ?? ''))->filter()->values();
                                                            $codeList = collect(explode(', ', $item['codes'] ?? ''))->filter()->values();
                                                            $creator = $item['creator'] ?? null;
                                                        @endphp
                                                        <tbody class="trace-row">
                                                            <tr>
                                                                <td class="trace-no text-center">{{ $loop->iteration }}</td>

                                                                <td class="text-nowrap">
                                                                    <div class="trace-date">{{ $date->translatedFormat('d M Y') }}</div>
                                                                    <div class="trace-sub">{{ $date->translatedFormat('l') }}</div>
                                                                </td>

                                                                <td>
                                                                    @if ($item['shift'])
                                                                        <span class="trace-badge trace-badge--shift">{{ $item['shift'] }}</span>
                                                                    @else
                                                                        <span class="trace-empty-cell">-</span>
                                                                    @endif
                                                                </td>

                                                                <td class="text-nowrap">
                                                                    @if (!empty($item['time']))
                                                                        <i class="far fa-clock trace-cellicon"></i>{{ $item['time'] }}
                                                                    @else
                                                                        <span class="trace-empty-cell">-</span>
                                                                    @endif
                                                                </td>

                                                                <td>
                                                                    @if (!empty($item['area']))
                                                                        <span class="trace-badge trace-badge--area">{{ $item['area'] }}</span>
                                                                    @else
                                                                        <span class="trace-empty-cell">-</span>
                                                                    @endif
                                                                </td>

                                                                <td class="trace-hl">
                                                                    @forelse ($prodList->take(2) as $p)
                                                                        <span class="trace-chip trace-chip--product" title="{{ $p }}">{{ $p }}</span>
                                                                    @empty
                                                                        <span class="trace-empty-cell">-</span>
                                                                    @endforelse
                                                                    @if ($prodList->count() > 2)
                                                                        <span class="trace-more" title="{{ $prodList->slice(2)->implode(', ') }}">+{{ $prodList->count() - 2 }}</span>
                                                                    @endif
                                                                </td>

                                                                <td class="trace-hl">
                                                                    @forelse ($codeList->take(3) as $c)
                                                                        <span class="trace-chip trace-chip--code" title="{{ $c }}">{{ $c }}</span>
                                                                    @empty
                                                                        <span class="trace-empty-cell">-</span>
                                                                    @endforelse
                                                                    @if ($codeList->count() > 3)
                                                                        <span class="trace-more" title="{{ $codeList->slice(3)->implode(', ') }}">+{{ $codeList->count() - 3 }}</span>
                                                                    @endif
                                                                </td>

                                                                <td>
                                                                    @if ($creator)
                                                                        <span class="trace-user">
                                                                            <span class="trace-avatar">{{ mb_strtoupper(mb_substr($creator, 0, 1)) }}</span>
                                                                            <span class="trace-user__name">{{ $creator }}</span>
                                                                        </span>
                                                                    @else
                                                                        <span class="trace-empty-cell">-</span>
                                                                    @endif
                                                                </td>

                                                                <td class="text-center text-nowrap">
                                                                    <button type="button" class="trace-act trace-act--eye trace-eye"
                                                                        title="Lihat detail" data-toggle="collapse"
                                                                        data-target="#trace-detail-{{ $uid }}"
                                                                        aria-expanded="false">
                                                                        <i class="fas fa-eye icon-open"></i>
                                                                        <i class="fas fa-eye-slash icon-close"></i>
                                                                    </button>
                                                                    @if ($item['pdf_url'])
                                                                        <a href="{{ $item['pdf_url'] }}" target="_blank"
                                                                            class="trace-act trace-act--pdf" title="Export PDF">
                                                                            <i class="fas fa-file-pdf"></i>
                                                                        </a>
                                                                    @endif
                                                                </td>
                                                            </tr>

                                                            <tr id="trace-detail-{{ $uid }}" class="collapse trace-item-panel"
                                                                data-module="{{ $item['module'] }}"
                                                                data-form-key="{{ $item['form_key'] }}">
                                                                <td colspan="9" class="trace-detail-cell">
                                                                    <div class="trace-detail-body">
                                                                        <div class="trace-skeleton">
                                                                            <span></span><span></span><span></span>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    @endforeach
                                                </table>
                                            </div>

                                            <div class="trace-pager d-none">
                                                <div class="trace-pager__info"></div>
                                                <nav><ul class="pagination pagination-sm mb-0"></ul></nav>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
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
            const filters = $container.data('filters') || {};
            const cache = {}; // key: module::form_key
            const detailUrl = '{{ route('traceability.formDetails') }}';

            const skeletonHtml = '<div class="trace-skeleton"><span></span><span></span><span></span></div>';

            // ===== Paginasi per grup (client-side) =====
            $container.find('.trace-group__body').each(function () {
                const $body = $(this);
                const $table = $body.find('table.trace-table');
                const $items = $table.children('tbody.trace-row');
                const perPage = parseInt($table.data('per-page'), 10) || 20;
                const total = $items.length;
                const lastPage = Math.ceil(total / perPage);

                // Nomor urut mengikuti urutan dalam grup
                $items.each(function (i) { $(this).find('.trace-no').text(i + 1); });

                if (total <= perPage) return; // tidak perlu pager

                const $pager = $body.find('.trace-pager').removeClass('d-none');
                const $info = $pager.find('.trace-pager__info');
                const $ul = $pager.find('ul.pagination');
                let current = 1;

                function pageList() {
                    const pages = new Set([1, lastPage, current - 1, current, current + 1]);
                    const sorted = [...pages].filter(p => p >= 1 && p <= lastPage).sort((a, b) => a - b);
                    const out = [];
                    sorted.forEach((p, i) => {
                        if (i > 0 && p - sorted[i - 1] > 1) out.push('…');
                        out.push(p);
                    });
                    return out;
                }

                function render() {
                    const start = (current - 1) * perPage;
                    $items.hide().slice(start, start + perPage).show();

                    $info.text(`Menampilkan ${start + 1}–${Math.min(start + perPage, total)} dari ${total}`);

                    let html = `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                                    <a class="page-link" href="#" data-page="${current - 1}">&laquo;</a></li>`;
                    pageList().forEach(p => {
                        html += p === '…'
                            ? '<li class="page-item disabled"><span class="page-link">…</span></li>'
                            : `<li class="page-item ${p === current ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
                    });
                    html += `<li class="page-item ${current === lastPage ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${current + 1}">&raquo;</a></li>`;
                    $ul.html(html);
                }

                $ul.on('click', 'a.page-link', function (e) {
                    e.preventDefault();
                    const p = parseInt($(this).data('page'), 10);
                    if (!p || p < 1 || p > lastPage || p === current) return;
                    current = p;
                    render();

                    // Kembali ke awal grup ini bila header sudah keluar layar
                    const top = $table.closest('.trace-group')[0].getBoundingClientRect().top;
                    if (top < 0) {
                        $('html, body').animate({ scrollTop: window.pageYOffset + top - 80 }, 150);
                    }
                });

                render();
            });

            // Highlight kata kunci di baris tabel (kolom produk & kode)
            $container.find('td.trace-hl').each(function () {
                highlight($(this), search);
            });

            // Tandai baris yang sedang terbuka
            $container.on('show.bs.collapse hide.bs.collapse', '.trace-item-panel', function (e) {
                if (e.target !== this) return;
                $(this).closest('tbody.trace-row').toggleClass('is-open', e.type === 'show');
            });

            // Buka detail (hanya baris detail, bukan grup modul)
            $container.on('show.bs.collapse', '.trace-item-panel', function (e) {
                if (e.target !== this) return;

                const $panel = $(this);
                const $body = $panel.find('.trace-detail-body');
                const module = $panel.data('module');
                const formKey = $panel.data('form-key');
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
                        ...filters
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

            // Enter / Spasi membuka grup modul
            $container.on('keydown', '.trace-group__toggle', function (e) {
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

    {{-- Preset rentang tanggal (terpisah agar tidak terkena return awal di atas) --}}
    <script>
        $(function () {
            const iso = d => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);

            $('#traceRangePreset').on('change', function () {
                if (this.value === '') return;
                const days = parseInt(this.value, 10);
                const to = new Date();
                const from = new Date();
                from.setDate(to.getDate() - days);
                $('[name=date_from]').val(iso(from));
                $('[name=date_to]').val(iso(to));
                this.value = '';
            });
        });
    </script>

    <style>
        /* ===== Filter ===== */
        .trace-filter {
            background: #f8f9fb;
            border: 1px solid #e6e8ec;
            border-radius: .75rem;
            padding: .55rem .75rem .65rem;
        }

        .trace-filter__row {
            display: flex;
            align-items: flex-end;
            gap: .5rem;
            flex-wrap: nowrap;
        }

        .trace-filter__field {
            display: flex;
            flex-direction: column;
            gap: .2rem;
            min-width: 0;
        }

        .trace-filter__field label {
            margin: 0;
            font-size: .64rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .03em;
            white-space: nowrap;
        }
        

        .trace-filter__field--wide    { flex: 0 1 220px; }
        .trace-filter__field--product { flex: 0 1 200px; }
        .trace-filter__field--area { flex: 0 1 200px; }
        .trace-filter__field--shift   { flex: 0 1 200px; }
        .trace-filter__field--date    { flex: 0 1 200px; }
        .trace-filter__field--preset  { flex: 0 1 200px; }

        .trace-filter__input {
            width: 100%;
            height: 2rem;
            border: 1px solid #dde1e6;
            background: #fff;
            border-radius: .5rem;
            padding: 0 .5rem;
            font-size: .75rem;
            color: #1f2937;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .trace-filter__input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
        }

        .trace-filter__input.is-invalid { border-color: #dc3545; }

        /* Tombol: desktop hanya ikon */
        .trace-filter__buttons {
            display: flex;
            align-items: center;
            gap: .3rem;
            margin-left: auto;
            flex-shrink: 0;
        }

        .trace-filter__btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            min-width: 2rem;
            height: 2rem;
            padding: 0 .6rem;
            border-radius: .5rem;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s ease, border-color .15s ease, color .15s ease;
        }

        .trace-filter__btntext { display: none; }

        .trace-filter__btn--submit { border: 0; background: #4f46e5; color: #fff; }
        .trace-filter__btn--submit:hover { background: #4338ca; }

        .trace-filter__btn--reset {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #dc2626;
        }

        .trace-filter__btn--reset:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
            text-decoration: none;
        }

        @media (min-width: 1400px) {
            .trace-filter__field--wide    { flex-basis: 280px; }
            .trace-filter__field--product { flex-basis: 240px; }
        }

        /* Tablet: grid 2 kolom, tombol di baris terakhir rata kanan */
        @media (max-width: 992px) {
            .trace-filter__row {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .6rem .75rem;
                align-items: end;
            }

            .trace-filter__field { flex: none; width: 100%; }

            .trace-filter__field--wide,
            .trace-filter__field--product { grid-column: 1 / -1; }

            .trace-filter__buttons {
                grid-column: 1 / -1;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .6rem .75rem;
                margin-left: 0;
                padding-top: .25rem;
            }

            .trace-filter__field--area { grid-column: 1 / -1; }

            .trace-filter__buttons .trace-filter__btn {
                width: 100%;
                height: 2.1rem;
            }

            .trace-filter__buttons .trace-filter__btn--submit:only-child {
                grid-column: 1 / -1;
            }

            .trace-filter__btntext { display: inline; }
            .trace-filter__btn { padding: 0 1rem; }
        }

        /* Mobile: semua 100% */
        @media (max-width: 576px) {
            .trace-filter__row { grid-template-columns: 1fr; }

            .trace-filter__buttons { grid-template-columns: 1fr; }
            .trace-filter__buttons .trace-filter__btn--submit:only-child { grid-column: 1; }
        }

        /* ===== Chip filter aktif ===== */
        .trace-active {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .4rem;
            margin-top: .75rem;
            font-size: .78rem;
        }

        .trace-active__title { color: #6b7280; margin-right: .15rem; }

        .trace-active__chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .35rem .2rem .65rem;
            border-radius: 999px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            color: #3730a3;
        }

        .trace-active__label { color: #6366f1; }

        .trace-active__chip a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 50%;
            color: #4f46e5;
            text-decoration: none;
            transition: background .15s ease;
        }

        .trace-active__chip a:hover { background: #c7d2fe; }

        .trace-active__clear {
            margin-left: .25rem;
            color: #6b7280;
            text-decoration: underline;
        }

        .trace-active__clear:hover { color: #b91c1c; }

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

        .trace-summary__count { font-size: .9rem; color: #374151; }

        /* ===== Grup per modul ===== */
        .trace-group {
            background: #fff;
            border: 1px solid #e6e8ec;
            border-left: 4px solid hsl(var(--h) 60% 52%);
            border-radius: .75rem;
            margin-bottom: .75rem;
            overflow: hidden;
        }

        .trace-group__toggle {
            cursor: pointer;
            padding: 1rem 1.1rem;
            outline: none;
            background: hsl(var(--h) 60% 98%);
        }

        .trace-group__toggle:focus-visible { box-shadow: inset 0 0 0 2px #c7d2fe; }

        .trace-group__toggle::after {
            display: none !important;
            content: none !important;
        }

        .trace-group__toggle[aria-expanded="true"] { border-bottom: 1px solid #eef0f3; }

        .trace-group__toggle[aria-expanded="true"] .trace-arrow { transform: rotate(180deg); }

        .trace-group__body {
            padding: .75rem .75rem .75rem;
            background: #fafbfd;
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

        .trace-head__meta .fa-calendar-alt { margin-right: .25rem; opacity: .7; }

        .trace-head__actions {
            display: flex;
            align-items: center;
            gap: .75rem;
            flex-shrink: 0;
        }

        .trace-pill {
            padding: .1rem .5rem;
            border-radius: 999px;
            background: #f3f4f6;
            color: #4b5563;
            font-size: .72rem;
        }

        .trace-pill--match { background: #ecfdf5; color: #047857; }

        .trace-arrow { color: #9ca3af; transition: transform .2s ease; }

        /* ===== Tabel hasil per grup ===== */
        .trace-table {
            background: #fff;
            font-size: .8rem;
            margin-bottom: .5rem;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #e6e8ec;
            border-radius: .6rem;
            overflow: hidden;
        }

        .trace-table thead th {
            background: #f1f3f9;
            color: #6b7280;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: .65rem .75rem;
            border: 0;
            border-bottom: 1px solid #e6e8ec;
            vertical-align: middle;
            white-space: nowrap;
        }

        .trace-table td {
            padding: .6rem .75rem;
            vertical-align: middle;
            color: #374151;
            border: 0;
            border-bottom: 1px solid #f0f2f6;
        }

        .trace-table tbody.trace-row:last-of-type > tr:first-child td { border-bottom: 0; }

        .trace-table tbody.trace-row > tr:first-child { transition: background .12s ease; }
        .trace-table tbody.trace-row > tr:first-child:hover { background: #f8f9fe; }

        /* Baris terbuka: aksen warna modul */
        .trace-table tbody.trace-row.is-open > tr:first-child { background: hsl(var(--h) 70% 97%); }
        .trace-table tbody.trace-row.is-open > tr:first-child td:first-child {
            box-shadow: inset 3px 0 0 hsl(var(--h) 60% 52%);
        }

        .trace-no { color: #9ca3af; font-size: .75rem; }

        .trace-date { font-weight: 600; color: #1f2937; }
        .trace-sub { font-size: .68rem; color: #9ca3af; margin-top: .05rem; }

        .trace-cellicon { margin-right: .3rem; color: #9ca3af; font-size: .72rem; }
        .trace-empty-cell { color: #cbd0d8; }

        /* Badge & chip */
        .trace-badge {
            display: inline-block;
            padding: .15rem .55rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .trace-badge--shift { background: #fff7e6; color: #b45309; }
        .trace-badge--area { background: #eef2ff; color: #4338ca; }

        .trace-chip {
            display: inline-block;
            max-width: 190px;
            margin: .1rem .25rem .1rem 0;
            padding: .12rem .5rem;
            border-radius: .4rem;
            font-size: .74rem;
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .trace-chip--product { background: #ecfdf5; color: #047857; }

        .trace-chip--code {
            background: #f3f4f6;
            color: #374151;
            font-family: SFMono-Regular, Menlo, Consolas, monospace;
            font-size: .72rem;
            letter-spacing: .01em;
        }

        .trace-more {
            display: inline-block;
            padding: .1rem .4rem;
            border-radius: 999px;
            background: #e5e7eb;
            color: #4b5563;
            font-size: .68rem;
            font-weight: 600;
            cursor: help;
            vertical-align: middle;
        }

        /* Pembuat */
        .trace-user { display: inline-flex; align-items: center; gap: .45rem; }

        .trace-avatar {
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 50%;
            background: hsl(var(--h) 55% 90%);
            color: hsl(var(--h) 55% 30%);
            font-size: .7rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .trace-user__name { white-space: nowrap; }

        /* Tombol aksi */
        .trace-act {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.9rem;
            height: 1.9rem;
            margin: 0 .1rem;
            border-radius: 50%;
            border: 1px solid transparent;
            font-size: .8rem;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s ease, color .15s ease, box-shadow .15s ease, transform .1s ease;
        }

        .trace-act:hover { transform: translateY(-1px); text-decoration: none; }
        .trace-act:focus { outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, .2); }

        .trace-act--eye { background: #e0f2fe; color: #0369a1; }
        .trace-act--eye:hover { background: #bae6fd; color: #075985; }
        .trace-act--eye[aria-expanded="true"] { background: #0284c7; color: #fff; }

        .trace-act--pdf { background: #fff1f2; color: #be123c; }
        .trace-act--pdf:hover { background: #ffe4e6; color: #9f1239; }

        .trace-act .icon-close { display: none; }
        .trace-act[aria-expanded="true"] .icon-open { display: none; }
        .trace-act[aria-expanded="true"] .icon-close { display: inline; }

        /* Baris detail */
        .trace-table .trace-detail-cell {
            padding: 0;
            background: #fafbfd;
            border-bottom: 1px solid #e6e8ec;
        }

        .trace-table tbody.trace-row.is-open .trace-detail-cell {
            box-shadow: inset 3px 0 0 hsl(var(--h) 60% 52%);
        }

        /* ===== Pager di dalam grup ===== */
        .trace-pager {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
            padding: .5rem .25rem .75rem;
        }

        .trace-pager.d-none { display: none !important; }

        .trace-pager__info { font-size: .78rem; color: #6b7280; }

        .trace-pager .page-link {
            font-size: .78rem;
            color: #4b5563;
            border-color: #e6e8ec;
        }

        .trace-pager .page-item.active .page-link {
            background: hsl(var(--h) 55% 48%);
            border-color: hsl(var(--h) 55% 48%);
            color: #fff;
        }

        /* ===== Isi detail ===== */
        .trace-detail-body {
            padding: 1rem 1.1rem 1.1rem;
            background: #fff;
        }

        .trace-detail-body .table { font-size: .8rem; }

        .trace-detail-body .table th {
            background: #f3f4f8;
            color: #4b5563;
            font-weight: 600;
            vertical-align: middle;
        }

        .trace-detail-body .table td { vertical-align: middle; }

        .trace-detail-body h6 { font-size: .85rem; color: #374151; }

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

        .trace-error { color: #b91c1c; font-size: .85rem; }

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

        .trace-field-row:last-child { border-bottom: none; }
        .trace-field-row .label { color: #858796; }
        .trace-field-row .value { font-weight: 600; margin-left: .35rem; }

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