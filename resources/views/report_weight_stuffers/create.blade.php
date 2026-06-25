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
                        value="{{ session('shift_number') }}-{{ session('shift_group') }}">
                </div>
            </div>
        </div>

        {{-- ============================================================
             DATA PRODUK & MESIN (satu blok statis, tanpa JS clone)
        ============================================================ --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Data Produk</div>
            <div class="card-body">

                <div class="row g-3 mb-3">
                    <div class="col-md-6 mb-3">
                        <label>Nama Produk</label>
                        <select name="details[0][product_uuid]" class="form-select form-control select2-product" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->uuid }}"
                                    data-name="{{ $product->product_name }}">
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
                        <input type="text" name="details[0][production_code]" class="form-control" placeholder="mis: KP-2026-001" required>
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
                    <div class="col-md-6">
                        <label>Diameter Casing (mm)</label>
                        <input type="number" name="details[0][cases][0][actual_case_2]" class="form-control" placeholder="mis: 26">
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             BERAT PER 3 PCS
        ============================================================ --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Berat per 3 pcs (gr)</div>
            <div class="card-body">

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label>Standar Berat</label>
                        <input type="text" name="details[0][weight_standard]" class="form-control" placeholder="mis: 204-209">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="fw-bold">Berat Aktual (gr)</label>
                    <div class="d-flex flex-wrap gap-2 weight-wrapper" id="weightWrapper" style="gap:.8rem">
                        @for ($i = 1; $i <= 3; $i++)
                        <div class="weight-item">
                            <label style="font-size:13px">Berat {{ $i }}</label>
                            <input type="number" step="0.01"
                                name="details[0][weights][0][actual_weight_{{ $i }}]"
                                class="form-control weight-input" style="width:100px" placeholder="0">
                        </div>
                        @endfor
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2" id="addWeight">+ Tambah Berat</button>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label>Rata-rata Berat</label>
                        <input type="number" step="0.01" name="details[0][avg_weight]" class="form-control avg-weight" readonly>
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
                        <textarea name="details[0][weight_corrective_action]" class="form-control" rows="1"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label>Keterangan</label>
                        <textarea name="details[0][weight_notes]" class="form-control" rows="1"></textarea>
                    </div>
                </div>

            </div>
        </div>

        {{-- ============================================================
             PANJANG PER PCS
        ============================================================ --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Panjang per pcs (mm)</div>
            <div class="card-body">

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label>Standar Panjang</label>
                        <input type="text" name="details[0][long_standard]" class="form-control" placeholder="mis: 120-130">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="fw-bold">Panjang Aktual (mm)</label>
                    <div class="d-flex flex-wrap gap-2 long-wrapper" id="longWrapper" style="gap:.8rem">
                        @for ($i = 1; $i <= 3; $i++)
                        <div class="weight-item">
                            <label style="font-size:13px">Panjang {{ $i }}</label>
                            <input type="number" step="0.01"
                                name="details[0][weights][0][actual_long_{{ $i }}]"
                                class="form-control long-input" style="width:100px" placeholder="0">
                        </div>
                        @endfor
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2" id="addLong">+ Tambah Panjang</button>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label>Rata-rata Panjang</label>
                        <input type="number" step="0.01" name="details[0][avg_long]" class="form-control avg-long" readonly>
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
                        <textarea name="details[0][long_corrective_action]" class="form-control" rows="1"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label>Keterangan</label>
                        <textarea name="details[0][long_notes]" class="form-control" rows="1"></textarea>
                    </div>
                </div>

            </div>
        </div>

        {{-- ============================================================
             BERAT FLA
        ============================================================ --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Berat Fla (gr)</div>
            <div class="card-body">

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label>Standar Berat Fla</label>
                        <input type="text" name="details[0][fla_standard]" class="form-control" placeholder="mis: 12-13">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="fw-bold">Berat Fla Aktual (gr)</label>
                    <div class="d-flex flex-wrap gap-2 fla-wrapper" id="flaWrapper" style="gap:.8rem">
                        @for ($i = 1; $i <= 3; $i++)
                        <div class="weight-item">
                            <label style="font-size:13px">Fla {{ $i }}</label>
                            <input type="number" step="0.01"
                                name="details[0][weights][0][actual_fla_{{ $i }}]"
                                class="form-control fla-input" style="width:100px" placeholder="0">
                        </div>
                        @endfor
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2" id="addFla">+ Tambah Fla</button>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label>Rata-rata Fla</label>
                        <input type="number" step="0.01" name="details[0][avg_fla]" class="form-control avg-fla" readonly>
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
                        <textarea name="details[0][fla_corrective_action]" class="form-control" rows="1"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label>Keterangan</label>
                        <textarea name="details[0][fla_notes]" class="form-control" rows="1"></textarea>
                    </div>
                </div>

            </div>
        </div>

        {{-- ============================================================
             CATATAN & DOKUMENTASI
        ============================================================ --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Catatan & Dokumentasi</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label>Catatan</label>
                    <input type="text" name="details[0][notes]" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Dokumentasi</label>
                    <input type="file" name="details[0][documentation][]" class="form-control"
                        accept="image/*" multiple>
                </div>
            </div>
        </div>

        <div class="mb-4 d-flex justify-content-end gap-2">
            <a href="{{ route('report_weight_stuffers.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success px-4">Simpan Laporan</button>
        </div>

    </form>
</div>
@endsection

@section('script')
<script>
'use strict';

// ---------------------------------------------------------------
// Tambah input aktual dinamis
// ---------------------------------------------------------------
function addInput(wrapperId, type, labelText) {
    const wrapper = document.getElementById(wrapperId);
    const count   = wrapper.querySelectorAll('input').length + 1;

    const div = document.createElement('div');
    div.className = 'weight-item';
    div.innerHTML =
        '<label style="font-size:13px">' + labelText + ' ' + count + '</label>' +
        '<input type="number" step="0.01" ' +
            'name="details[0][weights][0][actual_' + type + '_' + count + ']" ' +
            'class="form-control ' + type + '-input" style="width:100px" placeholder="0">';
    wrapper.appendChild(div);
}

document.getElementById('addWeight').addEventListener('click', function () { addInput('weightWrapper', 'weight', 'Berat'); });
document.getElementById('addLong').addEventListener('click',   function () { addInput('longWrapper',   'long',   'Panjang'); });
document.getElementById('addFla').addEventListener('click',    function () { addInput('flaWrapper',    'fla',    'Fla'); });

// ---------------------------------------------------------------
// Hitung rata-rata otomatis
// ---------------------------------------------------------------
document.getElementById('mainForm').addEventListener('input', function (e) {
    if (!e.target.matches('input[type="number"]:not([readonly])')) return;

    [
        { cls: '.weight-input', avg: '.avg-weight' },
        { cls: '.long-input',   avg: '.avg-long'   },
        { cls: '.fla-input',    avg: '.avg-fla'    },
    ].forEach(function (cfg) {
        const inputs = document.querySelectorAll(cfg.cls);
        let sum = 0, count = 0;
        inputs.forEach(function (inp) {
            const v = parseFloat(inp.value);
            if (!isNaN(v)) { sum += v; count++; }
        });
        const avgEl = document.querySelector(cfg.avg);
        if (avgEl) avgEl.value = count ? (sum / count).toFixed(2) : '';
    });
});

// ---------------------------------------------------------------
// Compress gambar sebelum upload
// ---------------------------------------------------------------
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

// ---------------------------------------------------------------
// Submit: compress → kirim
// ---------------------------------------------------------------
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