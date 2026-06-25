@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_weight_stuffers.update', $report->uuid) }}" method="POST" id="mainForm" enctype="multipart/form-data">
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

        @foreach($report->details as $idx => $d)
        @php $stuffer = $stufferMap[$d->uuid] ?? null; @endphp

        {{-- ============================================================
            DATA PRODUK & MESIN
        ============================================================ --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Data Produk #{{ $idx + 1 }}</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6 mb-3">
                        <label>Nama Produk</label>
                        <select name="details[{{ $idx }}][product_uuid]" class="form-control select2-product" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->uuid }}"
                                    {{ $product->uuid === $d->product_uuid ? 'selected' : '' }}>
                                    {{ $product->product_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Gramase (gr)</label>
                        <input type="number" step="0.01" name="details[{{ $idx }}][gramase]" class="form-control"
                            value="{{ $d->gramase }}" placeholder="mis: 205">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Kode Produksi</label>
                        <input type="text" name="details[{{ $idx }}][production_code]" class="form-control"
                            value="{{ $d->production_code }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Nama Mesin</label>
                        <select name="details[{{ $idx }}][machine]" class="form-control" required>
                            <option value="">-- Pilih Mesin --</option>
                            @foreach(['townsend' => 'Townsend', 'hitech' => 'Hitech', 'vemag' => 'Vemag', 'vemag2' => 'Vemag 2', 'handtmann' => 'Handtmann'] as $val => $label)
                                <option value="{{ $val }}" {{ $d->machine === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Waktu Proses</label>
                        <input type="time" name="details[{{ $idx }}][time]" class="form-control"
                            value="{{ \Carbon\Carbon::parse($d->time)->format('H:i') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Diameter Casing (mm)</label>
                        <input type="number" name="details[{{ $idx }}][cases][0][actual_case_2]" class="form-control"
                            value="{{ $d->cases->first()?->actual_case_2 }}" placeholder="mis: 26">
                    </div>
                </div>
            </div>
        </div>

        {{-- BERAT PER 3 PCS --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Berat per 3 pcs (gr) — Produk #{{ $idx + 1 }}</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label>Standar Berat</label>
                        <input type="text" name="details[{{ $idx }}][weight_standard]" class="form-control"
                            value="{{ $d->weight_standard }}" placeholder="mis: 204-209">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="fw-bold">Berat Aktual (gr)</label>
                    <div class="d-flex flex-wrap gap-2 weight-wrapper" id="weightWrapper-{{ $idx }}" style="gap:.8rem">
                        @forelse($d->weights as $i => $w)
                        <div class="weight-item">
                            <label style="font-size:13px">Berat {{ $i + 1 }}</label>
                            <input type="number" step="0.01"
                                name="details[{{ $idx }}][weights][0][actual_weight_{{ $i + 1 }}]"
                                class="form-control weight-input-{{ $idx }}" style="width:100px"
                                value="{{ $w->actual_weight }}">
                        </div>
                        @empty
                            @for ($i = 1; $i <= 3; $i++)
                            <div class="weight-item">
                                <label style="font-size:13px">Berat {{ $i }}</label>
                                <input type="number" step="0.01"
                                    name="details[{{ $idx }}][weights][0][actual_weight_{{ $i }}]"
                                    class="form-control weight-input-{{ $idx }}" style="width:100px">
                            </div>
                            @endfor
                        @endforelse
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2 btn-add-weight" data-idx="{{ $idx }}">+ Tambah Berat</button>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label>Rata-rata Berat</label>
                        <input type="number" step="0.01" name="details[{{ $idx }}][avg_weight]" class="form-control avg-weight-{{ $idx }}"
                            value="{{ $stuffer?->avg_weight }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Status</label>
                        <select name="details[{{ $idx }}][weight_status]" class="form-control">
                            <option value="">-- Pilih Status --</option>
                            @foreach(['OK', 'NOT OK'] as $s)
                                <option value="{{ $s }}" {{ $d->weight_status === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Tindakan Koreksi</label>
                        <textarea name="details[{{ $idx }}][weight_corrective_action]" class="form-control" rows="1">{{ $d->weight_corrective_action }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label>Keterangan</label>
                        <textarea name="details[{{ $idx }}][weight_notes]" class="form-control" rows="1">{{ $d->weight_notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANJANG PER PCS --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Panjang per pcs (mm) — Produk #{{ $idx + 1 }}</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label>Standar Panjang</label>
                        <input type="text" name="details[{{ $idx }}][long_standard]" class="form-control"
                            value="{{ $d->long_standard }}" placeholder="mis: 120-130">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="fw-bold">Panjang Aktual (mm)</label>
                    <div class="d-flex flex-wrap gap-2 long-wrapper" id="longWrapper-{{ $idx }}" style="gap:.8rem">
                        @forelse($d->weights as $i => $w)
                        <div class="weight-item">
                            <label style="font-size:13px">Panjang {{ $i + 1 }}</label>
                            <input type="number" step="0.01"
                                name="details[{{ $idx }}][weights][0][actual_long_{{ $i + 1 }}]"
                                class="form-control long-input-{{ $idx }}" style="width:100px"
                                value="{{ $w->actual_long }}">
                        </div>
                        @empty
                            @for ($i = 1; $i <= 3; $i++)
                            <div class="weight-item">
                                <label style="font-size:13px">Panjang {{ $i }}</label>
                                <input type="number" step="0.01"
                                    name="details[{{ $idx }}][weights][0][actual_long_{{ $i }}]"
                                    class="form-control long-input-{{ $idx }}" style="width:100px">
                            </div>
                            @endfor
                        @endforelse
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2 btn-add-long" data-idx="{{ $idx }}">+ Tambah Panjang</button>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label>Rata-rata Panjang</label>
                        <input type="number" step="0.01" name="details[{{ $idx }}][avg_long]" class="form-control avg-long-{{ $idx }}"
                            value="{{ $stuffer?->avg_long }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Status</label>
                        <select name="details[{{ $idx }}][long_status]" class="form-control">
                            <option value="">-- Pilih Status --</option>
                            @foreach(['OK', 'NOT OK'] as $s)
                                <option value="{{ $s }}" {{ $d->long_status === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Tindakan Koreksi</label>
                        <textarea name="details[{{ $idx }}][long_corrective_action]" class="form-control" rows="1">{{ $d->long_corrective_action }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label>Keterangan</label>
                        <textarea name="details[{{ $idx }}][long_notes]" class="form-control" rows="1">{{ $d->long_notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- BERAT FLA --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Berat Fla (gr) — Produk #{{ $idx + 1 }}</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label>Standar Berat Fla</label>
                        <input type="text" name="details[{{ $idx }}][fla_standard]" class="form-control"
                            value="{{ $d->fla_standard }}" placeholder="mis: 12-13">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="fw-bold">Berat Fla Aktual (gr)</label>
                    <div class="d-flex flex-wrap gap-2 fla-wrapper" id="flaWrapper-{{ $idx }}" style="gap:.8rem">
                        @forelse($d->weights as $i => $w)
                        <div class="weight-item">
                            <label style="font-size:13px">Fla {{ $i + 1 }}</label>
                            <input type="number" step="0.01"
                                name="details[{{ $idx }}][weights][0][actual_fla_{{ $i + 1 }}]"
                                class="form-control fla-input-{{ $idx }}" style="width:100px"
                                value="{{ $w->actual_fla }}">
                        </div>
                        @empty
                            @for ($i = 1; $i <= 3; $i++)
                            <div class="weight-item">
                                <label style="font-size:13px">Fla {{ $i }}</label>
                                <input type="number" step="0.01"
                                    name="details[{{ $idx }}][weights][0][actual_fla_{{ $i }}]"
                                    class="form-control fla-input-{{ $idx }}" style="width:100px">
                            </div>
                            @endfor
                        @endforelse
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2 btn-add-fla" data-idx="{{ $idx }}">+ Tambah Fla</button>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label>Rata-rata Fla</label>
                        <input type="number" step="0.01" name="details[{{ $idx }}][avg_fla]" class="form-control avg-fla-{{ $idx }}"
                            value="{{ $stuffer?->avg_fla }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Status</label>
                        <select name="details[{{ $idx }}][fla_status]" class="form-control">
                            <option value="">-- Pilih Status --</option>
                            @foreach(['OK', 'NOT OK'] as $s)
                                <option value="{{ $s }}" {{ $d->fla_status === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Tindakan Koreksi</label>
                        <textarea name="details[{{ $idx }}][fla_corrective_action]" class="form-control" rows="1">{{ $d->fla_corrective_action }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label>Keterangan</label>
                        <textarea name="details[{{ $idx }}][fla_notes]" class="form-control" rows="1">{{ $d->fla_notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- CATATAN & DOKUMENTASI --}}
        <div class="card shadow mb-4">
            <div class="card-header fw-bold">Catatan & Dokumentasi — Produk #{{ $idx + 1 }}</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label>Catatan</label>
                    <input type="text" name="details[{{ $idx }}][notes]" class="form-control"
                        value="{{ $stuffer?->notes }}">
                </div>
                <div class="col-md-6">
                    <label>Upload Dokumentasi Baru</label>
                    <input type="file" name="details[{{ $idx }}][documentation][]" class="form-control"
                        accept="image/*" multiple>
                </div>
                @if($d->documentations->isNotEmpty())
                <div class="col-12">
                    <label class="fw-semibold" style="font-size:13px">Dokumentasi Tersimpan</label>
                    <div class="d-flex flex-wrap gap-2 mt-1" id="existingDocsWrapper-{{ $idx }}">
                        @foreach($d->documentations as $doc)
                        <div class="position-relative" id="docItem-{{ $doc->uuid }}">
                            <input type="hidden" name="details[{{ $idx }}][keep_docs][]" value="{{ $doc->uuid }}" class="keep-doc-input">
                            <a href="{{ Storage::url($doc->image) }}" target="_blank">
                                <img src="{{ Storage::url($doc->image) }}"
                                    style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">
                            </a>
                            <button type="button"
                                class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 btn-remove-doc"
                                style="width:18px;height:18px;font-size:10px;line-height:1"
                                data-uuid="{{ $doc->uuid }}"
                                data-idx="{{ $idx }}"
                                title="Hapus">&times;</button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        @endforeach

        <div class="mb-4 d-flex justify-content-end gap-2">
            <a href="{{ route('report_weight_stuffers.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success px-4">Update Laporan</button>
        </div>

    </form>
</div>
@endsection

@section('script')
<script>
'use strict';

// ---------------------------------------------------------------
// Tambah input dinamis per detail
// ---------------------------------------------------------------
function addInput(wrapperId, type, idx, labelText) {
    const wrapper = document.getElementById(wrapperId);
    const count   = wrapper.querySelectorAll('input').length + 1;
    const div     = document.createElement('div');
    div.className = 'weight-item';
    div.innerHTML =
        '<label style="font-size:13px">' + labelText + ' ' + count + '</label>' +
        '<input type="number" step="0.01" ' +
            'name="details[' + idx + '][weights][0][actual_' + type + '_' + count + ']" ' +
            'class="form-control ' + type + '-input-' + idx + '" style="width:100px" placeholder="0">';
    wrapper.appendChild(div);
}

document.querySelectorAll('.btn-add-weight').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const idx = this.dataset.idx;
        addInput('weightWrapper-' + idx, 'weight', idx, 'Berat');
    });
});
document.querySelectorAll('.btn-add-long').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const idx = this.dataset.idx;
        addInput('longWrapper-' + idx, 'long', idx, 'Panjang');
    });
});
document.querySelectorAll('.btn-add-fla').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const idx = this.dataset.idx;
        addInput('flaWrapper-' + idx, 'fla', idx, 'Fla');
    });
});

// ---------------------------------------------------------------
// Hitung rata-rata per detail (pakai closest card)
// ---------------------------------------------------------------
document.getElementById('mainForm').addEventListener('input', function(e) {
    if (!e.target.matches('input[type="number"]:not([readonly])')) return;

    // Cari idx dari class input (format: weight-input-0, long-input-1, dst)
    const classList = Array.from(e.target.classList);
    const matched = classList.find(c => /^(weight|long|fla)-input-\d+$/.test(c));
    if (!matched) return;

    const parts = matched.split('-');
    const type  = parts[0]; // weight / long / fla
    const idx   = parts[parts.length - 1];

    const inputs = document.querySelectorAll('.' + type + '-input-' + idx);
    let sum = 0, count = 0;
    inputs.forEach(function(inp) {
        const v = parseFloat(inp.value);
        if (!isNaN(v)) { sum += v; count++; }
    });
    const avgEl = document.querySelector('.avg-' + type + '-' + idx);
    if (avgEl) avgEl.value = count ? (sum / count).toFixed(2) : '';
});

// ---------------------------------------------------------------
// Hapus dokumentasi existing
// ---------------------------------------------------------------
document.querySelectorAll('.btn-remove-doc').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const uuid = this.dataset.uuid;
        const idx  = this.dataset.idx;
        const item = document.getElementById('docItem-' + uuid);
        if (item) item.remove();
        const wrapper = document.getElementById('existingDocsWrapper-' + idx);
        if (wrapper && wrapper.children.length === 0) {
            wrapper.closest('.col-12').style.display = 'none';
        }
    });
});

// ---------------------------------------------------------------
// Compress & submit
// ---------------------------------------------------------------
function compressImage(file, quality) {
    return new Promise((resolve, reject) => {
        if (!file.type.startsWith('image/')) { resolve(file); return; }
        const reader = new FileReader();
        reader.onerror = () => reject(new Error('Gagal membaca: ' + file.name));
        reader.onload = function(e) {
            const img = new Image();
            img.onerror = () => reject(new Error('Format tidak didukung: ' + file.name));
            img.onload = function() {
                let w = img.width, h = img.height;
                const MAX = 1280;
                if (w > h) { if (w > MAX) { h *= MAX / w; w = MAX; } }
                else        { if (h > MAX) { w *= MAX / h; h = MAX; } }
                const canvas = document.createElement('canvas');
                canvas.width = w; canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                canvas.toBlob(function(blob) {
                    if (!blob) { reject(new Error('Gagal compress: ' + file.name)); return; }
                    resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg' }));
                }, 'image/jpeg', quality);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

document.getElementById('mainForm').addEventListener('submit', async function(e) {
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