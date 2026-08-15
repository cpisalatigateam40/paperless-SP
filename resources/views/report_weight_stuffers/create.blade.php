@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Proses Stuffing', 'url' => route('report_weight_stuffers.index')],
        ['label' => 'Tambah Data', 'url' => null],
    ]" />

    <form action="{{ route('report_weight_stuffers.store') }}" method="POST" id="mainForm" enctype="multipart/form-data">
        @csrf

        {{-- HEADER LAPORAN --}}
        <div class="card shadow mb-4" >
            <div class="card-header fw-bold" style="margin-top: 0px !important;">Header Laporan</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>
                <div class="col-md-6">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control"
                        value="{{ session('shift_number') }}-{{ session('shift_group') }}">
                </div>
            </div>
        </div>

        {{-- CONTAINER BLOCK PEMERIKSAAN (REPEATABLE) --}}
        <div id="detailsContainer">
            <div class="detail-block" data-index="0">

                <div class="card shadow mb-3" style="margin-top: 48px;">
                    <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                        <span>Detail Produk</span>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-detail-block" style="display:none">
                            Hapus Pemeriksaan Ini
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 mb-3">
                                <label>Nama Produk</label>
                                <select name="details[0][product_uuid]" class="form-select form-control select2-product" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->uuid }}" data-name="{{ $product->product_name }}">
                                            {{ $product->product_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Gramase (gr)</label>
                                <input type="number" step="0.01" name="details[0][gramase]" class="form-control" placeholder="mis: 205">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Kode Produksi</label>
                                <input type="text" name="details[0][production_code]" class="form-control" placeholder="mis: QD14317" required>
                            </div>
                            <div class="col-md-6">
                                <label>Nama Mesin</label>
                                <select name="details[0][machine]" class="form-select form-control" required>
                                    <option value="">-- Pilih Mesin --</option>
                                    <option value="townsend">Townsend</option>
                                    <option value="hitech">Hitech</option>
                                    <option value="vemag">Vemag</option>
                                    <option value="vemag2">Vemag 2</option>
                                    <option value="handtmann">Handtmann</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Waktu Proses</label>
                                <input type="time" name="details[0][time]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Diameter Casing (mm)</label>
                                <input type="number" name="details[0][cases][0][actual_case_2]" class="form-control" placeholder="mis: 26">
                            </div>
                            <div class="col-md-6">
                                <label>Stuffer Speed</label>
                                <input type="number" name="details[0][stuffer_speed]" class="form-control" placeholder="mis: 180">
                            </div>
                        </div>

                        <h5 class="mt-5" style="font-weight: bold;">Berat per 3 pcs (gr)</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label>Standar Berat</label>
                                <input type="text" name="details[0][weight_standard]" class="form-control" placeholder="mis: 204-209">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="fw-bold">Berat Aktual (gr)</label>
                            <div class="d-flex flex-wrap gap-2 weight-wrapper" style="gap:.8rem">
                                @for ($i = 1; $i <= 3; $i++)
                                <div class="weight-item">
                                    <label style="font-size:13px">Berat {{ $i }}</label>
                                    <input type="number" step="0.01"
                                        name="details[0][weights][0][actual_weight_{{ $i }}]"
                                        class="form-control weight-input" style="width:100px" placeholder="0">
                                </div>
                                @endfor
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2 add-weight-btn">+ Tambah Berat</button>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4 mb-3">
                                <label>Rata-rata Berat</label>
                                <input type="number" step="0.01" name="details[0][avg_weight]" class="form-control avg-weight" placeholder="terisi otomatis" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Status</label>
                                <select name="details[0][weight_status]" class="form-control">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="OK">OK</option>
                                    <option value="NOT OK">NOT OK</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Tindakan Koreksi</label>
                                <textarea name="details[0][weight_corrective_action]" class="form-control" rows="1" placeholder="masukkan tindakan koreksi"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label>Keterangan</label>
                                <textarea name="details[0][weight_notes]" class="form-control" rows="1" placeholder="masukkan keterangan"></textarea>
                            </div>
                        </div>


                        <h5 class="mt-5" style="font-weight: bold;">Panjang per pcs (mm)</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label>Standar Panjang</label>
                                <input type="text" name="details[0][long_standard]" class="form-control" placeholder="mis: 120-130">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="fw-bold">Panjang Aktual (mm)</label>
                            <div class="d-flex flex-wrap gap-2 long-wrapper" style="gap:.8rem">
                                @for ($i = 1; $i <= 3; $i++)
                                <div class="weight-item">
                                    <label style="font-size:13px">Panjang {{ $i }}</label>
                                    <input type="number" step="0.01"
                                        name="details[0][weights][0][actual_long_{{ $i }}]"
                                        class="form-control long-input" style="width:100px" placeholder="0">
                                </div>
                                @endfor
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2 add-long-btn">+ Tambah Panjang</button>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4 mb-3">
                                <label>Rata-rata Panjang</label>
                                <input type="number" step="0.01" name="details[0][avg_long]" class="form-control avg-long" placeholder="terisi otomatis" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Status</label>
                                <select name="details[0][long_status]" class="form-control">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="OK">OK</option>
                                    <option value="NOT OK">NOT OK</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Tindakan Koreksi</label>
                                <textarea name="details[0][long_corrective_action]" class="form-control" rows="1" placeholder="masukkan tindakan koreksi"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label>Keterangan</label>
                                <textarea name="details[0][long_notes]" class="form-control" rows="1" placeholder="masukkan keterangan"></textarea>
                            </div>
                        </div>

                        <h5 class="mt-5" style="font-weight: bold;">Berat Fla (gr)</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label>Standar Berat Fla</label>
                                <input type="text" name="details[0][fla_standard]" class="form-control" placeholder="mis: 12-13">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="fw-bold">Berat Fla Aktual (gr)</label>
                            <div class="d-flex flex-wrap gap-2 fla-wrapper" style="gap:.8rem">
                                @for ($i = 1; $i <= 3; $i++)
                                <div class="weight-item">
                                    <label style="font-size:13px">Fla {{ $i }}</label>
                                    <input type="number" step="0.01"
                                        name="details[0][weights][0][actual_fla_{{ $i }}]"
                                        class="form-control fla-input" style="width:100px" placeholder="0">
                                </div>
                                @endfor
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2 add-fla-btn">+ Tambah Fla</button>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4 mb-3">
                                <label>Rata-rata Fla</label>
                                <input type="number" step="0.01" name="details[0][avg_fla]" class="form-control avg-fla" placeholder="terisi otomatis" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Status</label>
                                <select name="details[0][fla_status]" class="form-control">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="OK">OK</option>
                                    <option value="NOT OK">NOT OK</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Tindakan Koreksi</label>
                                <textarea name="details[0][fla_corrective_action]" class="form-control" rows="1" placeholder="masukkan tindakan koreksi"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label>Keterangan</label>
                                <textarea name="details[0][fla_notes]" class="form-control" rows="1" placeholder="masukkan keterangan"></textarea>
                            </div>
                        </div>

                        <h5 class="mt-5" style="font-weight: bold;">Catatan & Dokumentasi</h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Catatan</label>
                                <input type="text" name="details[0][notes]" class="form-control" placeholder="masukkan catatan">
                            </div>
                            <div class="col-md-6">
                                <label>Dokumentasi</label>
                                <input type="file" name="details[0][documentation][]" class="form-control" accept="image/*" multiple>
                            </div>
                        </div>





                    </div>
                </div>

            </div> {{-- /.detail-block --}}
        </div> {{-- /#detailsContainer --}}

        <div class="mb-4">
            <button type="button" id="addDetailBlock" class="btn btn-outline-primary">
                + Tambah Pemeriksaan
            </button>
            <small class="text-muted d-block mt-1">
                Nama produk & kode produksi akan otomatis tersalin dari pemeriksaan sebelumnya.
            </small>
        </div>

        <div class="mt-3">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-success px-4">Simpan</button>
        </div>

    </form>
</div>
@endsection

@section('script')
<script>
'use strict';

const detailsContainer = document.getElementById('detailsContainer');
let detailIndex = 0;

// Simpan markup block pertama SEBELUM select2 diinisialisasi,
// supaya bisa di-clone bersih tanpa bawaan select2.
const blockTemplateHtml = detailsContainer.querySelector('.detail-block').outerHTML;

function initSelect2(scope) {
    $(scope).find('.select2-product').select2({
        width: '100%',
        dropdownParent: $(scope)
    });
}

// Panggil select2 untuk block pertama (yang sudah ada di HTML)
initSelect2(detailsContainer.querySelector('.detail-block'));

const headerColors = ['#e3f2fd',  '#e8f5e9', '#fff3e0', '#fce4ec', '#ede7f6', '#e0f7fa'];

function applyHeaderColor(blockEl, index) {
    const header = blockEl.querySelector('.card-header');
    if (header) header.style.backgroundColor = headerColors[index % headerColors.length];
}

// Warna untuk block pertama (index 0)
applyHeaderColor(detailsContainer.querySelector('.detail-block'), 0);

function reindexBlock(blockEl, index) {
    blockEl.dataset.index = index;
    blockEl.querySelectorAll('[name]').forEach(function (el) {
        el.name = el.name.replace(/details\[\d+\]/, 'details[' + index + ']');
    });
}

function resetWeightGroup(blockEl, type, labelText, index) {
    const wrapper = blockEl.querySelector('.' + type + '-wrapper');
    wrapper.innerHTML = '';
    for (let i = 1; i <= 3; i++) {
        wrapper.insertAdjacentHTML('beforeend',
            '<div class="weight-item">' +
                '<label style="font-size:13px">' + labelText + ' ' + i + '</label>' +
                '<input type="number" step="0.01" ' +
                    'name="details[' + index + '][weights][0][actual_' + type + '_' + i + ']" ' +
                    'class="form-control ' + type + '-input" style="width:100px" placeholder="0">' +
            '</div>'
        );
    }
}

document.getElementById('addDetailBlock').addEventListener('click', function () {
    detailIndex++;

    const temp = document.createElement('div');
    temp.innerHTML = blockTemplateHtml.trim();
    const newBlock = temp.firstElementChild;

    reindexBlock(newBlock, detailIndex);

    // Kosongkan semua input di block baru
    newBlock.querySelectorAll('input:not([type=file]), textarea').forEach(function (el) { el.value = ''; });
    newBlock.querySelectorAll('select').forEach(function (el) { el.value = ''; });
    newBlock.querySelectorAll('input[type=file]').forEach(function (el) { el.value = ''; });

    // Reset ulang grup berat/panjang/fla ke default 3 kolom kosong
    resetWeightGroup(newBlock, 'weight', 'Berat', detailIndex);
    resetWeightGroup(newBlock, 'long', 'Panjang', detailIndex);
    resetWeightGroup(newBlock, 'fla', 'Fla', detailIndex);

    // Default waktu proses = sekarang
    const timeInput = newBlock.querySelector('input[type="time"]');
    if (timeInput) {
        const now = new Date();
        timeInput.value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    }

    // Copy Nama Produk, Kode Produksi, Gramase dari block terakhir sebelumnya
    const lastBlock = detailsContainer.querySelector('.detail-block:last-of-type');
    const prevProductSelect = lastBlock.querySelector('.select2-product');
    const prevCode = lastBlock.querySelector('input[name$="[production_code]"]');
    const prevGramase = lastBlock.querySelector('input[name$="[gramase]"]');

    newBlock.querySelector('.select2-product').value = prevProductSelect.value;
    newBlock.querySelector('input[name$="[production_code]"]').value = prevCode.value;
    newBlock.querySelector('input[name$="[gramase]"]').value = prevGramase.value;

    newBlock.querySelector('.remove-detail-block').style.display = 'inline-block';

    applyHeaderColor(newBlock, detailIndex);

    detailsContainer.appendChild(newBlock);
    initSelect2(newBlock);

    newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

// Hapus block pemeriksaan (event delegation)
detailsContainer.addEventListener('click', function (e) {
    if (!e.target.matches('.remove-detail-block')) return;
    e.target.closest('.detail-block').remove();
});

// Tombol "+ Tambah Berat/Panjang/Fla" per block (event delegation)
detailsContainer.addEventListener('click', function (e) {
    const btn = e.target.closest('.add-weight-btn, .add-long-btn, .add-fla-btn');
    if (!btn) return;

    const block = btn.closest('.detail-block');
    const index = block.dataset.index;

    let type, label, wrapperClass;
    if (btn.matches('.add-weight-btn')) { type = 'weight'; label = 'Berat'; wrapperClass = 'weight-wrapper'; }
    if (btn.matches('.add-long-btn'))   { type = 'long';   label = 'Panjang'; wrapperClass = 'long-wrapper'; }
    if (btn.matches('.add-fla-btn'))    { type = 'fla';    label = 'Fla'; wrapperClass = 'fla-wrapper'; }

    const wrapper = block.querySelector('.' + wrapperClass);
    const count = wrapper.querySelectorAll('input').length + 1;

    wrapper.insertAdjacentHTML('beforeend',
        '<div class="weight-item">' +
            '<label style="font-size:13px">' + label + ' ' + count + '</label>' +
            '<input type="number" step="0.01" ' +
                'name="details[' + index + '][weights][0][actual_' + type + '_' + count + ']" ' +
                'class="form-control ' + type + '-input" style="width:100px" placeholder="0">' +
        '</div>'
    );
});

// Hitung rata-rata, DI-SCOPE PER BLOCK (fix bug lama: dulu global)
document.getElementById('mainForm').addEventListener('input', function (e) {
    if (!e.target.matches('input[type="number"]:not([readonly])')) return;

    const block = e.target.closest('.detail-block');
    if (!block) return;

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
});

// Compress gambar sebelum upload (tidak berubah, tetap jalan untuk semua block)
function compressImage(file, quality) {
    return new Promise((resolve, reject) => {
        if (!file.type.startsWith('image/')) { resolve(file); return; }
        const reader = new FileReader();
        reader.onerror = () => reject(new Error('Gagal membaca: ' + file.name));
        reader.onload = function (e) {
            const img = new Image();
            img.onerror = () => reject(new Error('Format tidak didukung: ' + file.name));
            img.onload = function () {
                let w = img.width, h = img.height;
                const MAX = 1280;
                if (w > h) { if (w > MAX) { h *= MAX / w; w = MAX; } }
                else        { if (h > MAX) { w *= MAX / h; h = MAX; } }
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

document.getElementById('mainForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    for (const input of this.querySelectorAll('input[type="file"]')) {
        if (!input.files || !input.files.length) continue;
        const dt = new DataTransfer();
        for (const file of input.files) {
            let out;
            try   { out = await compressImage(file, 0.7); }
            catch { out = file; }
            dt.items.add(out);
        }
        input.files = dt.files;
    }

    this.submit();
});
</script>
@endsection