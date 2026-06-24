@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_weight_stuffers.store') }}" method="POST" id="mainForm" enctype="multipart/form-data">
        @csrf

        {{-- ============================================================
             HEADER LAPORAN
        ============================================================ --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Header Laporan</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>
                <div class="col-md-6">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control"
                        value="{{ session('shift_number') }}-{{ session('shift_group') }}" readonly>
                </div>
            </div>
        </div>

        {{-- ============================================================
             HEADER PRODUK  (hanya dibaca satu kali, disalin ke tiap blok mesin via JS)
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
                                @endif>
                                {{ $product->product_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Gramase (gr)</label>
                    <input type="number" step="0.01" id="headerGramase" class="form-control" placeholder="mis: 205">
                </div>
                <div class="col-md-3">
                    <label>Kode Produksi</label>
                    <input type="text" id="headerProdCode" class="form-control" placeholder="mis: KP-2026-001">
                </div>
            </div>
        </div>

        {{-- ============================================================
             CONTAINER BLOK MESIN (diisi secara dinamis oleh JS)
        ============================================================ --}}
        <div id="mesinContainer"></div>

        {{-- Tombol tambah mesin --}}
        <div class="text-right">
            <button type="button" class="btn btn-outline-primary" id="btnAddMesin">
                + Tambah Mesin
            </button>
        </div>

        <div class="mb-4 d-flex justify-content-start">
            <button type="submit" class="btn btn-success px-4">Simpan Laporan</button>
        </div>
    </form>
</div>

{{-- ============================================================
     TEMPLATE BLOK MESIN  (hidden, di-clone oleh JS)
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
            <input type="hidden" name="details[__N__][product_uuid]" class="copy-product-uuid">
            <input type="hidden" name="details[__N__][gramase]"       class="copy-gramase">
            <input type="hidden" name="details[__N__][production_code]" class="copy-production-code">

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
                    <input type="number" name="details[__N__][cases][0][actual_case_2]" class="form-control" placeholder="mis: 205">
                </div>
            </div>

            <hr class="my-4">

            {{-- ---- BERAT PER 3 PCS ---- --}}
            <h6 class="mb-3"><strong>Berat per 3 pcs (gr)</strong></h6>
            <div class="row g-3 mb-2">
                <div class="col-md-4">
                    <label>Standar Berat</label>
                    <input type="text" name="details[__N__][weight_standard]" class="form-control" placeholder="mis: 204-209">
                </div>
            </div>
            <div class="mb-2">
                <label class="fw-bold">Berat Aktual (gr)</label>
                <div class="d-flex flex-wrap gap-2 weight-wrapper" id="weightWrapper-__N__" style="gap: .8rem;">
                    <div class="weight-item">
                        <label style="font-size:16px">Berat 1</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_weight_1]"
                            class="form-control weight-input" style="width:100px" placeholder="mis: 20">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:16px">Berat 2</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_weight_2]"
                            class="form-control weight-input" style="width:100px" placeholder="mis: 20">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:16px">Berat 3</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_weight_3]"
                            class="form-control weight-input" style="width:100px" placeholder="mis: 20">
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
                    <textarea name="details[__N__][weight_corrective_action]" class="form-control" rows="1" placeholder="masukkan tindakan koreksi"></textarea>
                </div>
                <div class="col-md-4">
                    <label>Keterangan</label>
                    <textarea name="details[__N__][weight_notes]" class="form-control" rows="1" placeholder="masukkan keterangan"></textarea>
                </div>
            </div>

            <hr class="my-4">

            {{-- ---- PANJANG PER PCS ---- --}}
            <h6 class="mb-3"><strong>Panjang per pcs (mm)</strong></h6>
            <div class="row g-3 mb-2">
                <div class="col-md-4">
                    <label>Standar Panjang</label>
                    <input type="text" name="details[__N__][long_standard]" class="form-control" placeholder="mis: 120-130">
                </div>
            </div>
            <div class="mb-2">
                <label class="fw-bold">Panjang Aktual (mm)</label>
                <div class="d-flex flex-wrap gap-2 long-wrapper" id="longWrapper-__N__" style="gap: .8rem;">
                    <div class="weight-item">
                        <label style="font-size:16px">Panjang 1</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_long_1]"
                            class="form-control long-input" style="width:100px" placeholder="mis: 20">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:16px">Panjang 2</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_long_2]"
                            class="form-control long-input" style="width:100px" placeholder="mis: 20">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:16px">Panjang 3</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_long_3]"
                            class="form-control long-input" style="width:100px" placeholder="mis: 20">
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
                    <textarea name="details[__N__][long_corrective_action]" class="form-control" rows="1" placeholder="masukkan tindakan koreksi"></textarea>
                </div>
                <div class="col-md-4">
                    <label>Keterangan</label>
                    <textarea name="details[__N__][long_notes]" class="form-control" rows="1" placeholder="masukkan keterangan"></textarea>
                </div>
            </div>

            <hr class="my-4">

            {{-- ---- BERAT FLA ---- --}}
            <h6 class="mb-3"><strong>Berat Fla (gr)</strong></h6>
            <div class="row g-3 mb-2">
                <div class="col-md-4">
                    <label>Standar Berat Fla</label>
                    <input type="text" name="details[__N__][fla_standard]" class="form-control" placeholder="mis: 12-13">
                </div>
            </div>
            <div class="mb-2">
                <label class="fw-bold">Berat Fla Aktual (gr)</label>
                <div class="d-flex flex-wrap gap-2 fla-wrapper" id="flaWrapper-__N__" style="gap: .8rem;">
                    <div class="weight-item">
                        <label style="font-size:16px">Fla 1</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_fla_1]"
                            class="form-control fla-input" style="width:100px" placeholder="mis: 20">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:16px">Fla 2</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_fla_2]"
                            class="form-control fla-input" style="width:100px" placeholder="mis: 20">
                    </div>
                    <div class="weight-item">
                        <label style="font-size:16px">Fla 3</label>
                        <input type="number" step="0.01" name="details[__N__][weights][0][actual_fla_3]"
                            class="form-control fla-input" style="width:100px" placeholder="mis: 20">
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
                    <textarea name="details[__N__][fla_corrective_action]" class="form-control" rows="1" placeholder="masukkan tindakan koreksi"></textarea>
                </div>
                <div class="col-md-4">
                    <label>Keterangan</label>
                    <textarea name="details[__N__][fla_notes]" class="form-control" rows="1" placeholder="masukkan keterangan"></textarea>
                </div>
            </div>

            <hr class="my-3">

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Catatan</label>
                    <input type="text" name="details[__N__][notes]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Dokumentasi</label>
                    <input type="file"
                        name="details[__N__][documentation][]"
                        class="form-control"
                        accept="image/*"
                        multiple>
                </div>
            </div>

        </div>{{-- /card-body --}}
    </div>{{-- /card --}}
</template>
@endsection

@section('script')
<script>
'use strict';

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

// Wire semua header input agar sync otomatis
document.getElementById('headerProduct').addEventListener('change', syncAllBlocks);
document.getElementById('headerGramase').addEventListener('input',  syncAllBlocks);
document.getElementById('headerProdCode').addEventListener('input',  syncAllBlocks);

// ---------------------------------------------------------------
// Tambah blok mesin baru
// ---------------------------------------------------------------
document.getElementById('btnAddMesin').addEventListener('click', function () {
    const tpl = document.getElementById('mesinTemplate').innerHTML;
    const n   = mesinIndex++;
    const h   = getHeaderValues();

    const html = tpl
        .replace(/__N__/g,         n)
        .replace(/__DISPLAY_N__/g, n + 1);

    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    const block = wrapper.firstElementChild;

    block.querySelector('.copy-product-uuid').value    = h.product_uuid;
    block.querySelector('.copy-gramase').value         = h.gramase;
    block.querySelector('.copy-production-code').value = h.production_code;

    block.querySelector('.info-product-name').textContent    = h.product_name    || '—';
    block.querySelector('.info-gramase').textContent         = h.gramase         || '—';
    block.querySelector('.info-production-code').textContent = h.production_code || '—';

    block.querySelector('.select-mesin').addEventListener('change', function () {
        block.querySelector('.mesin-label').textContent = this.options[this.selectedIndex].text;
    });

    block.querySelector('.btn-remove-mesin').addEventListener('click', function () {
        block.remove();
        renumberBlocks();
    });

    block.querySelectorAll('.btn-add-weight').forEach(function (btn) {
        btn.addEventListener('click', function () {
            addActualInput(block, n, this.dataset.type);
        });
    });

    block.addEventListener('input', function (e) {
        if (e.target.matches('input[type="number"]:not([readonly])')) {
            recalcAvg(block);
        }
    });

    document.getElementById('mesinContainer').appendChild(block);
});

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
        '<label style="font-size:16px">' + labelMap[type] + ' ' + count + '</label>' +
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
// Compress gambar sebelum upload
// ---------------------------------------------------------------
const COMPRESS_QUALITY = 0.7;

function compressImage(file, quality) {
    return new Promise((resolve, reject) => {
        if (!file.type.startsWith('image/')) { resolve(file); return; }

        const reader = new FileReader();
        reader.onerror = () => reject(new Error('Gagal membaca file: ' + file.name));
        reader.onload = function (e) {
            const img = new Image();
            img.onerror = () => reject(new Error('Format tidak didukung: ' + file.name));
            img.onload = function () {
                let w = img.width, h = img.height;
                const MAX_DIM = 1280;
                if (w > h) { if (w > MAX_DIM) { h *= MAX_DIM / w; w = MAX_DIM; } }
                else        { if (h > MAX_DIM) { w *= MAX_DIM / h; h = MAX_DIM; } }

                const canvas = document.createElement('canvas');
                canvas.width = w; canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);

                canvas.toBlob(function (blob) {
                    if (!blob) { reject(new Error('Gagal compress: ' + file.name)); return; }
                    resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg' }));
                }, 'image/jpeg', quality);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

// ---------------------------------------------------------------
// Submit: validasi → compress → kirim
// ---------------------------------------------------------------
document.getElementById('mainForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = this;

    if (document.querySelectorAll('.mesin-block').length === 0) {
        alert('Tambahkan minimal satu blok mesin sebelum menyimpan.');
        return;
    }
    if (!document.getElementById('headerProduct').value) {
        alert('Pilih produk di Header Produk sebelum menyimpan.');
        document.getElementById('headerProduct').focus();
        return;
    }

    syncAllBlocks();

    for (const input of form.querySelectorAll('input[type="file"]')) {
        if (!input.files || !input.files.length) continue;

        const dt = new DataTransfer();
        for (const file of input.files) {
            let compressed;
            try   { compressed = await compressImage(file, COMPRESS_QUALITY); }
            catch { compressed = file; }
            dt.items.add(compressed);
        }
        input.files = dt.files;
    }

    form.submit();
});
</script>
@endsection