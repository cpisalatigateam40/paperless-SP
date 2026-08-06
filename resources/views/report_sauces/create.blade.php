@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Tambah Verifikasi Proses Pemasakan di Steam Kettle</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('report_sauces.store') }}" method="POST">
                @csrf

                {{-- HEADER REPORT --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" id="shift" name="shift" class="form-control" value="{{ session('shift_number') }}-{{ session('shift_group') }}" required>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label>Produk</label>
                        <select name="product_uuid" id="product-select" class="form-control select2-product" required>
                            <option value="">-- pilih produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Formula</label>
                        <select name="formula_uuid" id="formula-select" class="form-control" required>
                            <option value="">-- Pilih Formula --</option>
                            {{-- Diisi via JavaScript --}}
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gramase</label>
                        <input type="number" step="0.01" name="gramase" class="form-control"
                            placeholder="Masukkan gramase" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control" placeholder="mis: QD15601AA0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Waktu Start</label>
                        <input type="time" name="start_time" id="start_time" class="form-control"
                            value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Waktu Stop</label>
                        <input type="time" name="end_time" id="end_time" class="form-control"
                            value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                    </div>
                </div>

                <h6 class="mt-4">Detail Proses</h6>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Pukul</label>
                        <input type="time" name="details[0][time]" class="form-control"
                            value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Durasi Proses</label>
                        <input type="text" name="details[0][process_step]" id="process_step" class="form-control" placeholder="masukkan durasi proses">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nomor Mesin</label>
                        <input type="text" name="details[0][no_mesin]" class="form-control" placeholder="mis: 1">
                    </div>
                </div>

                {{-- RAW MATERIALS --}}
                <h6 class="mt-3">Bahan Baku &amp; Premix</h6>
                <div id="raw-materials-wrapper">
                    {{-- Diisi otomatis via JS setelah formula dipilih --}}
                </div>

                <div class="row mb-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kenampakan</label>
                        <select name="details[0][appearance]" class="form-control" required>
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Warna</label>
                        <select name="details[0][color]" class="form-control" required>

                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Aroma</label>
                        <select name="details[0][aroma]" class="form-control" required>

                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Rasa</label>
                        <select name="details[0][taste]" class="form-control" required>

                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tekstur</label>
                        <select name="details[0][texture]" class="form-control" required>

                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4 mt-4">
                    <div class="col-md-6">
                        <label class="form-label d-block">Mixing Paddle</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[0][mixing_paddle]" value="on"
                                id="mixingOn0">
                            <label class="form-check-label" for="mixingOn0">On</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="details[0][mixing_paddle]" value="off"
                                id="mixingOff0">
                            <label class="form-check-label" for="mixingOff0">Off</label>
                        </div>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lama Proses (menit)</label>
                        <input type="number" step="0.01" name="details[0][duration]" class="form-control" placeholder="mis: 6">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pressure (Bar)</label>
                        <input type="number" step="0.01" name="details[0][pressure]" class="form-control" placeholder="mis: 6.5">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Target Temperature (&deg;C)</label>
                        <input type="number" step="0.01" name="details[0][target_temperature]" class="form-control" placeholder="mis: 6.5">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Actual Temperature (&deg;C)</label>
                        <input type="number" step="0.01" name="details[0][actual_temperature]" class="form-control" placeholder="mis: 6.5">
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status Produk</label>
                        <select name="details[0][product_status]" class="form-control" required>
                            <option value="Release">Release</option>
                            <option value="Reject">Reject</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tindakan Perbaikan</label>
                        <input type="text" name="details[0][corrective_action]" class="form-control" placeholder="masukkan tindakan perbaikan">
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="details[0][notes]" class="form-control" placeholder="masukkan catatan">
                    </div>
                </div>

                

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Catatan &amp; Dokumentasi</label>
                        <textarea name="documentation_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function () {
    $('#product-select').on('change', function () {
        const productUuid = this.value;
        const formulaSelect = document.getElementById('formula-select');
        const wrapper = document.getElementById('raw-materials-wrapper');
        const getFormulasUrl = "{{ route('report_sauces.getFormulas', ['productUuid' => 'PRODUCT_UUID_PLACEHOLDER']) }}";

        formulaSelect.innerHTML = '<option value="">-- Pilih Formula --</option>';
        wrapper.innerHTML = '';

        if (!productUuid) return;

        fetch(getFormulasUrl.replace('PRODUCT_UUID_PLACEHOLDER', productUuid))
            .then(res => res.json())
            .then(data => {
                data.formulas.forEach(formula => {
                    const opt = document.createElement('option');
                    opt.value = formula.uuid;
                    opt.textContent = formula.formula_name;
                    formulaSelect.appendChild(opt);
                });
            });
    });
});

document.getElementById('formula-select').addEventListener('change', function () {
    const formulaUuid = this.value;
    const wrapper = document.getElementById('raw-materials-wrapper');
    const getFormulationsUrl = @json(route('report_sauces.getFormulations', ['formulaUuid' => 'FORMULA_UUID_PLACEHOLDER']));

    wrapper.innerHTML = '';

    if (!formulaUuid) return;

    fetch(getFormulationsUrl.replace('FORMULA_UUID_PLACEHOLDER', formulaUuid))
        .then(res => res.json())
        .then(data => {
            const buildRow = (fm, label) => `
                <div class="row mb-3 raw-material-item">
                    <div class="col-md-4 mt-3">
                        <label class="form-label">${label} (Standard: ${fm.weight} kg)</label>
                        <input type="text" class="form-control" value="${fm.raw_material?.material_name ?? fm.premix?.name ?? '-'}" readonly>
                        <input type="hidden" name="details[0][raw_materials][${fm.uuid}][formulation_uuid]" value="${fm.uuid}">
                    </div>
                    <div class="col-md-4 mt-3">
                        <label class="form-label">Berat Aktual (Kg)</label>
                        <input type="number" step="0.01"
                            name="details[0][raw_materials][${fm.uuid}][amount]"
                            class="form-control" value="${fm.weight}">
                    </div>
                    <div class="col-md-4 mt-3">
                        <label class="form-label">Status</label>
                        <select name="details[0][raw_materials][${fm.uuid}][sensory]" class="form-control" required>
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-4 mt-3">
                        <label class="form-label">Tindakan Koreksi</label>
                        <input type="text" name="details[0][raw_materials][${fm.uuid}][corrective_action]" class="form-control" placeholder="masukkan tindakan koreksi">
                    </div>
                    <div class="col-md-4 mt-3">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="details[0][raw_materials][${fm.uuid}][keterangan]" class="form-control" placeholder="masukkan keterangan">
                    </div>
                </div>
            `;

            data.raw_materials.forEach(fm => wrapper.insertAdjacentHTML('beforeend', buildRow(fm, 'Bahan Baku')));
            data.premixes.forEach(fm => wrapper.insertAdjacentHTML('beforeend', buildRow(fm, 'Premix')));
        });
});

function calcDuration() {
    const startVal = document.getElementById('start_time').value;
    const endVal = document.getElementById('end_time').value;
    const processInput = document.getElementById('process_step');

    // hapus suffix durasi lama, baik format "- 15 menit" maupun "15 menit" polos
    const baseText = processInput.value.replace(/\s*-?\s*\d+\s*menit\s*$/i, '').trim();

    if (!startVal || !endVal) {
        processInput.value = baseText;
        return;
    }

    const [startH, startM] = startVal.split(':').map(Number);
    const [endH, endM] = endVal.split(':').map(Number);

    let diff = (endH * 60 + endM) - (startH * 60 + startM);
    if (diff < 0) diff += 24 * 60; // lewat tengah malam

    processInput.value = baseText ? `${baseText} - ${diff} menit` : `${diff} menit`;
}

document.getElementById('start_time').addEventListener('input', calcDuration);
document.getElementById('end_time').addEventListener('input', calcDuration);
document.addEventListener('DOMContentLoaded', calcDuration);
</script>
@endsection