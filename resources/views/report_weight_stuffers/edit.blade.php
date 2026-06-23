@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_weight_stuffers.update', $report->uuid) }}" method="POST" id="mainForm">
        @csrf
        @method('PUT')

        {{-- ============================================================
             HEADER LAPORAN
        ============================================================ --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Header Laporan</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ $report->date ? \Carbon\Carbon::parse($report->date)->toDateString() : '' }}" required>
                </div>
                <div class="col-md-6">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control"
                        value="{{ $report->shift }}" readonly>
                </div>
            </div>
        </div>

        {{-- ============================================================
             HEADER PRODUK (satu kali, disalin ke tiap blok mesin via JS)
        ============================================================ --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Header Produk</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label>Nama Produk</label>
                    <select id="headerProduct" class="form-select form-control select2-product">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            @php $standard = $standards->where('product_uuid', $product->uuid)->first(); @endphp
                            <option value="{{ $product->uuid }}"
                                data-name="{{ $product->product_name }}"
                                @if($standard)
                                    data-weight-standard="{{ $standard->weight_max }}"
                                    data-long-min="{{ $standard->long_min }}"
                                    data-long-max="{{ $standard->long_max }}"
                                    data-diameter="{{ $standard->diameter }}"
                                @endif
                                {{-- Pre-select dari detail pertama --}}
                                {{ $report->details->first() && $product->uuid == $report->details->first()->product_uuid ? 'selected' : '' }}>
                                {{ $product->product_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Gramase (gr)</label>
                    <input type="number" step="0.01" id="headerGramase" class="form-control"
                        value="{{ $report->details->first()->gramase ?? '' }}"
                        placeholder="mis: 205">
                </div>
                <div class="col-md-3">
                    <label>Kode Produksi</label>
                    <input type="text" id="headerProdCode" class="form-control"
                        value="{{ $report->details->first()->production_code ?? '' }}"
                        placeholder="mis: KP-2026-001">
                </div>
            </div>
        </div>

        {{-- ============================================================
             CONTAINER BLOK MESIN
        ============================================================ --}}
        <div id="mesinContainer">
            {{-- Akan di-render via JS dari data PHP yang di-encode ke JSON --}}
        </div>

        <div class="text-right mb-3">
            <button type="button" class="btn btn-outline-primary" id="btnAddMesin">
                + Tambah Mesin
            </button>
        </div>

        <div class="mb-4 d-flex justify-content-start">
            <button type="submit" class="btn btn-success px-4">Update Laporan</button>
        </div>
    </form>
</div>

{{-- ============================================================
     TEMPLATE BLOK MESIN (hidden, di-clone oleh JS)
     Gunakan __N__ sebagai placeholder index
============================================================ --}}
<template id="mesinTemplate">
    <div class="card shadow mb-3 mesin-block" id="mesinBlock-__N__">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                Blok Mesin #<span class="mesin-number">__DISPLAY_N__</span>
                &mdash;
                <span class="badge bg-info text-dark mesin-label">belum dipilih</span>
            </span>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-mesin">
                &times; Hapus
            </button>
        </div>
        <div class="card-body">

            {{-- hidden: data produk disalin dari header --}}
            <input type="hidden" name="details[__N__][product_uuid]"    class="copy-product-uuid">
            <input type="hidden" name="details[__N__][gramase]"          class="copy-gramase">
            <input type="hidden" name="details[__N__][production_code]"  class="copy-production-code">

            {{-- Info produk (read-only display) --}}
            <div class="alert alert-light py-2 px-3 mb-3 d-none" style="font-size:13px">
                Produk: <strong class="info-product-name text-dark">—</strong>
                &nbsp;·&nbsp;
                Gramase: <strong class="info-gramase text-dark">—</strong> gr
                &nbsp;·&nbsp;
                Kode: <strong class="info-production-code text-dark">—</strong>
            </div>

            {{-- ---- MESIN & WAKTU ---- --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label>Nama Mesin</label>
                    <select name="details[__N__][machine]" class="form-select form-control select-mesin" required>
                        <option value="">-- Pilih Mesin --</option>
                        <option value="townsend">Townsend</option>
                        <option value="hitech">Hitech</option>
                        <option value="vemag">Vemag</option>
                        <option value="vemag2">Vemag 2</option>
                        <option value="handtmann">Handtmann</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Waktu Proses</label>
                    <input type="time" name="details[__N__][time]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Diameter Casing (mm)</label>
                    <input type="number" name="details[__N__][cases][0][actual_case_2]" class="form-control">
                </div>
            </div>

            <hr class="my-4">

            {{-- ---- BERAT PER 3 PCS ---- --}}
            <h6 class="mb-3"><strong>Berat per 3 pcs (gr)</strong></h6>
            <div class="row g-3 mb-2">
                <div class="col-md-4">
                    <label>Standar Berat</label>
                    <input type="text" name="details[__N__][weight_standard]" class="form-control" placeholder="mis: 204-209" required>
                </div>
            </div>
            <div class="mb-2">
                <label class="fw-bold">Berat Aktual (gr)</label>
                <div class="d-flex flex-wrap gap-2 weight-wrapper" id="weightWrapper-__N__" style="gap:.8rem">
                    <div class="weight-item">
                        <label style="font-size:12px">Berat 1</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_weight_1]"
                            class="form-control weight-input" style="width:100px">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:12px">Berat 2</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_weight_2]"
                            class="form-control weight-input" style="width:100px">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:12px">Berat 3</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_weight_3]"
                            class="form-control weight-input" style="width:100px">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-secondary mt-2 btn-add-weight" data-type="weight">
                    + Tambah Berat
                </button>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label>Rata-rata Berat</label>
                    <input type="number" step="0.01" name="details[__N__][avg_weight]" class="form-control avg-weight" readonly>
                </div>
                <div class="col-md-4">
                    <label>Status</label>
                    <select name="details[__N__][weight_status]" class="form-control">
                        <option value="">-- Pilih Status --</option>
                        <option value="OK">OK</option>
                        <option value="NOT OK">NOT OK</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Tindakan Koreksi</label>
                    <textarea name="details[__N__][weight_corrective_action]" class="form-control" rows="1"></textarea>
                </div>
                <div class="col-md-4">
                    <label>Keterangan</label>
                    <textarea name="details[__N__][weight_notes]" class="form-control" rows="1"></textarea>
                </div>
            </div>

            <hr class="my-4">

            {{-- ---- PANJANG PER PCS ---- --}}
            <h6 class="mb-3"><strong>Panjang per pcs (mm)</strong></h6>
            <div class="row g-3 mb-2">
                <div class="col-md-4">
                    <label>Standar Panjang</label>
                    <input type="text" name="details[__N__][long_standard]" class="form-control" placeholder="mis: 120-130" required>
                </div>
            </div>
            <div class="mb-2">
                <label class="fw-bold">Panjang Aktual (mm)</label>
                <div class="d-flex flex-wrap gap-2 long-wrapper" id="longWrapper-__N__" style="gap:.8rem">
                    <div class="weight-item">
                        <label style="font-size:12px">Panjang 1</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_long_1]"
                            class="form-control long-input" style="width:100px">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:12px">Panjang 2</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_long_2]"
                            class="form-control long-input" style="width:100px">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:12px">Panjang 3</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_long_3]"
                            class="form-control long-input" style="width:100px">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-secondary mt-2 btn-add-weight" data-type="long">
                    + Tambah Panjang
                </button>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label>Rata-rata Panjang</label>
                    <input type="number" step="0.01" name="details[__N__][avg_long]" class="form-control avg-long" readonly>
                </div>
                <div class="col-md-4">
                    <label>Status</label>
                    <select name="details[__N__][long_status]" class="form-control">
                        <option value="">-- Pilih Status --</option>
                        <option value="OK">OK</option>
                        <option value="NOT OK">NOT OK</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Tindakan Koreksi</label>
                    <textarea name="details[__N__][long_corrective_action]" class="form-control" rows="1"></textarea>
                </div>
                <div class="col-md-4">
                    <label>Keterangan</label>
                    <textarea name="details[__N__][long_notes]" class="form-control" rows="1"></textarea>
                </div>
            </div>

            <hr class="my-4">

            {{-- ---- BERAT FLA ---- --}}
            <h6 class="mb-3"><strong>Berat Fla (gr)</strong></h6>
            <div class="row g-3 mb-2">
                <div class="col-md-4">
                    <label>Standar Berat Fla</label>
                    <input type="text" name="details[__N__][fla_standard]" class="form-control" placeholder="mis: 12-13" required>
                </div>
            </div>
            <div class="mb-2">
                <label class="fw-bold">Berat Fla Aktual (gr)</label>
                <div class="d-flex flex-wrap gap-2 fla-wrapper" id="flaWrapper-__N__" style="gap:.8rem">
                    <div class="weight-item">
                        <label style="font-size:12px">Fla 1</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_fla_1]"
                            class="form-control fla-input" style="width:100px">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:12px">Fla 2</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_fla_2]"
                            class="form-control fla-input" style="width:100px">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:12px">Fla 3</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_fla_3]"
                            class="form-control fla-input" style="width:100px">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-secondary mt-2 btn-add-weight" data-type="fla">
                    + Tambah Fla
                </button>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label>Rata-rata Fla</label>
                    <input type="number" step="0.01" name="details[__N__][avg_fla]" class="form-control avg-fla" readonly>
                </div>
                <div class="col-md-4">
                    <label>Status</label>
                    <select name="details[__N__][fla_status]" class="form-control">
                        <option value="">-- Pilih Status --</option>
                        <option value="OK">OK</option>
                        <option value="NOT OK">NOT OK</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Tindakan Koreksi</label>
                    <textarea name="details[__N__][fla_corrective_action]" class="form-control" rows="1"></textarea>
                </div>
                <div class="col-md-4">
                    <label>Keterangan</label>
                    <textarea name="details[__N__][fla_notes]" class="form-control" rows="1"></textarea>
                </div>
            </div>

            <hr class="my-3">

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Catatan</label>
                    <input type="text" name="details[__N__][notes]" class="form-control">
                </div>
            </div>

        </div>{{-- /card-body --}}
    </div>{{-- /card --}}
</template>
@endsection

@section('script')
<script>
(function () {
    'use strict';

    // ---------------------------------------------------------------
    // Data existing dari server (untuk pre-populate blok mesin)
    // ---------------------------------------------------------------
    const existingDetails = @json($detailsJson);

    let mesinIndex = 0;

    // ---------------------------------------------------------------
    // Ambil nilai dari header produk
    // ---------------------------------------------------------------
    function getHeaderValues() {
        const sel = document.getElementById('headerProduct');
        const opt = sel.options[sel.selectedIndex];
        return {
            product_uuid    : sel.value,
            product_name    : opt ? opt.dataset.name || opt.text : '—',
            gramase         : document.getElementById('headerGramase').value,
            production_code : document.getElementById('headerProdCode').value,
        };
    }

    // ---------------------------------------------------------------
    // Sync semua blok mesin yang sudah ada saat header berubah
    // ---------------------------------------------------------------
    function syncAllBlocks() {
        const h = getHeaderValues();
        document.querySelectorAll('.mesin-block').forEach(function (block) {
            block.querySelector('.copy-product-uuid').value    = h.product_uuid;
            block.querySelector('.copy-gramase').value         = h.gramase;
            block.querySelector('.copy-production-code').value = h.production_code;

            block.querySelector('.info-product-name').textContent    = h.product_name    || '—';
            block.querySelector('.info-gramase').textContent         = h.gramase         || '—';
            block.querySelector('.info-production-code').textContent = h.production_code || '—';
        });
    }

    document.getElementById('headerProduct').addEventListener('change', syncAllBlocks);
    document.getElementById('headerGramase').addEventListener('input',  syncAllBlocks);
    document.getElementById('headerProdCode').addEventListener('input',  syncAllBlocks);

    // ---------------------------------------------------------------
    // Buat blok mesin dari template
    // ---------------------------------------------------------------
    function createBlock(n) {
        const tpl  = document.getElementById('mesinTemplate').innerHTML;
        const h    = getHeaderValues();

        const html = tpl
            .replace(/__N__/g,         n)
            .replace(/__DISPLAY_N__/g, n + 1);

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        const block = wrapper.firstElementChild;

        // Isi hidden & info display
        block.querySelector('.copy-product-uuid').value    = h.product_uuid;
        block.querySelector('.copy-gramase').value         = h.gramase;
        block.querySelector('.copy-production-code').value = h.production_code;

        block.querySelector('.info-product-name').textContent    = h.product_name    || '—';
        block.querySelector('.info-gramase').textContent         = h.gramase         || '—';
        block.querySelector('.info-production-code').textContent = h.production_code || '—';

        // Label badge saat mesin dipilih
        block.querySelector('.select-mesin').addEventListener('change', function () {
            block.querySelector('.mesin-label').textContent = this.options[this.selectedIndex].text;
        });

        // Tombol hapus
        block.querySelector('.btn-remove-mesin').addEventListener('click', function () {
            block.remove();
            renumberBlocks();
        });

        // Tombol tambah aktual
        block.querySelectorAll('.btn-add-weight').forEach(function (btn) {
            btn.addEventListener('click', function () {
                addActualInput(block, n, this.dataset.type);
            });
        });

        // Hitung rata-rata
        block.addEventListener('input', function (e) {
            if (e.target.matches('input[type="number"]:not([readonly])')) {
                recalcAvg(block);
            }
        });

        return block;
    }

    // ---------------------------------------------------------------
    // Pre-populate blok dari data existing
    // ---------------------------------------------------------------
    function populateBlock(block, n, data) {
        // Mesin
        const selMesin = block.querySelector('.select-mesin');
        if (data.machine) {
            selMesin.value = data.machine;
            block.querySelector('.mesin-label').textContent =
                selMesin.options[selMesin.selectedIndex]?.text || data.machine;
        }

        // Waktu
        const timeInput = block.querySelector(`[name="details[${n}][time]"]`);
        if (timeInput && data.time) timeInput.value = data.time;

        // Diameter casing
        const caseInput = block.querySelector(`[name="details[${n}][cases][0][actual_case_2]"]`);
        if (caseInput && data.cases_actual_case_2 != null) caseInput.value = data.cases_actual_case_2;

        // Standar
        setVal(block, `details[${n}][weight_standard]`, data.weight_standard);
        setVal(block, `details[${n}][long_standard]`,   data.long_standard);
        setVal(block, `details[${n}][fla_standard]`,    data.fla_standard);

        // Status
        setVal(block, `details[${n}][weight_status]`, data.weight_status);
        setVal(block, `details[${n}][long_status]`,   data.long_status);
        setVal(block, `details[${n}][fla_status]`,    data.fla_status);

        // Corrective action & notes
        setVal(block, `details[${n}][weight_corrective_action]`, data.weight_corrective_action);
        setVal(block, `details[${n}][weight_notes]`,             data.weight_notes);
        setVal(block, `details[${n}][long_corrective_action]`,   data.long_corrective_action);
        setVal(block, `details[${n}][long_notes]`,               data.long_notes);
        setVal(block, `details[${n}][fla_corrective_action]`,    data.fla_corrective_action);
        setVal(block, `details[${n}][fla_notes]`,                data.fla_notes);

        // Avg (dari machine data)
        setVal(block, `details[${n}][avg_weight]`, data.avg_weight);
        setVal(block, `details[${n}][avg_long]`,   data.avg_long);
        setVal(block, `details[${n}][avg_fla]`,    data.avg_fla);

        // Notes mesin
        setVal(block, `details[${n}][notes]`, data.notes);

        // Measurements (weight / long / fla)
        if (data.weights && data.weights.length > 0) {
            const types = [
                { key: 'actual_weight', wrapperClass: '.weight-wrapper', inputClass: 'weight-input', label: 'Berat' },
                { key: 'actual_long',   wrapperClass: '.long-wrapper',   inputClass: 'long-input',   label: 'Panjang' },
                { key: 'actual_fla',    wrapperClass: '.fla-wrapper',    inputClass: 'fla-input',    label: 'Fla' },
            ];

            types.forEach(function (t) {
                const wrapper     = block.querySelector(t.wrapperClass);
                const existing    = wrapper.querySelectorAll('input');
                const extraNeeded = data.weights.length - existing.length;

                // Tambah input kalau data lebih banyak dari default 3
                for (let ex = 0; ex < extraNeeded; ex++) {
                    const idx = existing.length + ex + 1;
                    const div = document.createElement('div');
                    div.className = 'weight-item';
                    div.innerHTML =
                        '<label style="font-size:12px">' + t.label + ' ' + idx + '</label>' +
                        '<input type="number" step="0.01" ' +
                            'name="details[' + n + '][weights][0][' + t.key + '_' + idx + ']" ' +
                            'class="form-control ' + t.inputClass + '" style="width:100px">';
                    wrapper.appendChild(div);
                }

                // Isi nilai
                wrapper.querySelectorAll('input').forEach(function (inp, idx) {
                    const val = data.weights[idx] ? data.weights[idx][t.key] : null;
                    if (val != null) inp.value = val;
                });
            });

            recalcAvg(block);
        }
    }

    function setVal(block, name, value) {
        if (value == null || value === '') return;
        const el = block.querySelector(`[name="${name}"]`);
        if (el) el.value = value;
    }

    // ---------------------------------------------------------------
    // Tambah kolom aktual (weight / long / fla)
    // ---------------------------------------------------------------
    function addActualInput(block, n, type) {
        const wrapperClass = { weight: '.weight-wrapper', long: '.long-wrapper', fla: '.fla-wrapper' }[type];
        const wrapper      = block.querySelector(wrapperClass);
        const count        = wrapper.querySelectorAll('input').length + 1;
        const labelMap     = { weight: 'Berat', long: 'Panjang', fla: 'Fla' };

        const div = document.createElement('div');
        div.className = 'weight-item';
        div.innerHTML =
            '<label style="font-size:12px">' + labelMap[type] + ' ' + count + '</label>' +
            '<input type="number" step="0.01" ' +
                'name="details[' + n + '][weights][0][actual_' + type + '_' + count + ']" ' +
                'class="form-control ' + type + '-input" style="width:100px">';
        wrapper.appendChild(div);
    }

    // ---------------------------------------------------------------
    // Hitung rata-rata
    // ---------------------------------------------------------------
    function recalcAvg(block) {
        [
            { cls: '.weight-input', avg: '.avg-weight' },
            { cls: '.long-input',   avg: '.avg-long'   },
            { cls: '.fla-input',    avg: '.avg-fla'    },
        ].forEach(function (cfg) {
            const inputs = block.querySelectorAll(cfg.cls);
            let sum = 0, count = 0;
            inputs.forEach(function (inp) {
                const v = parseFloat(inp.value);
                if (!isNaN(v)) { sum += v; count++; }
            });
            const avgEl = block.querySelector(cfg.avg);
            if (avgEl) avgEl.value = count ? (sum / count).toFixed(2) : '';
        });
    }

    // ---------------------------------------------------------------
    // Renumber setelah blok dihapus
    // ---------------------------------------------------------------
    function renumberBlocks() {
        document.querySelectorAll('.mesin-block .mesin-number').forEach(function (el, i) {
            el.textContent = i + 1;
        });
    }

    // ---------------------------------------------------------------
    // Tombol tambah mesin (blok kosong)
    // ---------------------------------------------------------------
    document.getElementById('btnAddMesin').addEventListener('click', function () {
        const n     = mesinIndex++;
        const block = createBlock(n);
        document.getElementById('mesinContainer').appendChild(block);
    });

    // ---------------------------------------------------------------
    // Validasi submit
    // ---------------------------------------------------------------
    document.getElementById('mainForm').addEventListener('submit', function (e) {
        if (document.querySelectorAll('.mesin-block').length === 0) {
            e.preventDefault();
            alert('Tambahkan minimal satu blok mesin sebelum menyimpan.');
            return;
        }
        const productUuid = document.getElementById('headerProduct').value;
        if (!productUuid) {
            e.preventDefault();
            alert('Pilih produk di Header Produk sebelum menyimpan.');
            document.getElementById('headerProduct').focus();
            return;
        }
        syncAllBlocks();
    });

    // ---------------------------------------------------------------
    // Init: render blok existing dari data server
    // ---------------------------------------------------------------
    (function init() {
        existingDetails.forEach(function (data) {
            const n     = mesinIndex++;
            const block = createBlock(n);
            document.getElementById('mesinContainer').appendChild(block);
            populateBlock(block, n, data);
        });
    })();

})();
</script>
@endsection