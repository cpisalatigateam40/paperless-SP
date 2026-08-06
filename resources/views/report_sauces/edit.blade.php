@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Verifikasi Proses Pemasakan di Steam Kettle</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('report_sauces.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- HEADER REPORT --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::parse($report->date)->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" id="shift" name="shift" class="form-control" value="{{ $report->shift }}"
                            required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label>Produk</label>
                        <select name="product_uuid" id="product-select" class="form-control select2-product" required>
                            <option value="">-- pilih produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->uuid }}"
                                {{ $product->uuid == $report->product_uuid ? 'selected' : '' }}>
                                {{ $product->product_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Formula</label>
                        <select name="formula_uuid" id="formula-select" class="form-control" required>
                            <option value="">-- Pilih Formula --</option>
                            @foreach($formulas as $formula)
                            <option value="{{ $formula->uuid }}" {{ $formula->uuid == $report->formula_uuid ? 'selected' : '' }}>
                                {{ $formula->formula_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                                <label class="form-label">Gramase</label>
                                <input type="number" 
                                    step="0.01" 
                                    name="gramase" 
                                    class="form-control"
                                    value="{{ $report->gramase }}"
                                    placeholder="Masukkan gramase">
                            </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control"
                            value="{{ $report->production_code }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Waktu Start</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" value="{{ $report->start_time }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Waktu Stop</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" value="{{ $report->end_time }}">
                    </div>
                </div>

                <h6 class="mt-4">Detail Proses</h6>

                @foreach($report->details as $detailIndex => $detail)
                <div class="border p-3 rounded mb-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Pukul</label>
                            <input type="time" name="details[{{ $detailIndex }}][time]" class="form-control"
                                value="{{ $detail->time }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durasi Proses</label>
                            <input type="text" name="details[{{ $detailIndex }}][process_step]" class="form-control process-step-input"
                                value="{{ $detail->process_step }}">
                        </div>
                        <div class="col-md-6">
                            <label>Nomor Mesin</label>
                            <input type="text"
                                name="details[{{ $detailIndex }}][no_mesin]"
                                class="form-control"
                                value="{{ $detail->no_mesin }}"
                                placeholder="Masukkan nomor mesin">
                        </div>
                    </div>

                    <h6>Bahan Baku &amp; Premix</h6>
                    <div id="raw-materials-wrapper-{{ $detailIndex }}" class="raw-materials-wrapper">
                        @foreach($detail->rawMaterials as $rm)
                        <div class="row mb-3 raw-material-item">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ $rm->material_type === 'raw' ? 'Bahan Baku' : 'Premix' }} (Standard: {{ $rm->formulation?->weight }} kg)</label>
                                <input type="text" class="form-control"
                                    value="{{ $rm->formulation?->rawMaterial?->material_name ?? $rm->formulation?->premix?->name ?? '-' }}"
                                    readonly>
                                <input type="hidden"
                                    name="details[{{ $detailIndex }}][raw_materials][{{ $rm->uuid }}][formulation_uuid]"
                                    value="{{ $rm->formulation_uuid }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Berat Aktual (Kg)</label>
                                <input type="number" step="0.01"
                                    name="details[{{ $detailIndex }}][raw_materials][{{ $rm->uuid }}][amount]"
                                    class="form-control" value="{{ $rm->amount }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="details[{{ $detailIndex }}][raw_materials][{{ $rm->uuid }}][sensory]" class="form-control" required>
                                    <option value="OK" {{ $rm->sensory == 'OK' ? 'selected' : '' }}>OK</option>
                                    <option value="Tidak OK" {{ $rm->sensory == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tindakan Koreksi</label>
                                <input type="text"
                                    name="details[{{ $detailIndex }}][raw_materials][{{ $rm->uuid }}][corrective_action]"
                                    class="form-control" value="{{ $rm->corrective_action }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Keterangan</label>
                                <input type="text"
                                    name="details[{{ $detailIndex }}][raw_materials][{{ $rm->uuid }}][keterangan]"
                                    class="form-control" value="{{ $rm->keterangan }}">
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label>Kenampakan</label>
                            <select name="details[{{ $detailIndex }}][appearance]" class="form-control" required>
                                <option value="OK" {{ $detail->appearance == 'OK' ? 'selected' : '' }}>OK</option>
                                <option value="Tidak OK" {{ $detail->appearance == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Warna</label>
                            <select name="details[{{ $detailIndex }}][color]" class="form-control" required>
                                <option value="OK" {{ $detail->color == 'OK' ? 'selected' : '' }}>OK</option>
                                <option value="Tidak OK" {{ $detail->color == 'Tidak OK' ? 'selected' : '' }}>Tidak OK
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Aroma</label>
                            <select name="details[{{ $detailIndex }}][aroma]" class="form-control" required>
                                <option value="OK" {{ $detail->aroma == 'OK' ? 'selected' : '' }}>OK</option>
                                <option value="Tidak OK" {{ $detail->aroma == 'Tidak OK' ? 'selected' : '' }}>Tidak OK
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Rasa</label>
                            <select name="details[{{ $detailIndex }}][taste]" class="form-control" required>
                                <option value="OK" {{ $detail->taste == 'OK' ? 'selected' : '' }}>OK</option>
                                <option value="Tidak OK" {{ $detail->taste == 'Tidak OK' ? 'selected' : '' }}>Tidak OK
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Tekstur</label>
                            <select name="details[{{ $detailIndex }}][texture]" class="form-control" required>
                                <option value="OK" {{ $detail->texture == 'OK' ? 'selected' : '' }}>OK</option>
                                <option value="Tidak OK" {{ $detail->texture == 'Tidak OK' ? 'selected' : '' }}>Tidak OK
                                </option>
                            </select>
                        </div>

                        
                        <div class="col-md-12 mb-3 mt-3">
                            <label class="form-label d-block">Mixing Paddle</label>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                    type="radio"
                                    name="details[{{ $detailIndex }}][mixing_paddle]"
                                    id="mixingOn{{ $detailIndex }}"
                                    value="on"
                                    {{ $detail->mixing_paddle_on ? 'checked' : '' }}>
                                <label class="form-check-label" for="mixingOn{{ $detailIndex }}">On</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                    type="radio"
                                    name="details[{{ $detailIndex }}][mixing_paddle]"
                                    id="mixingOff{{ $detailIndex }}"
                                    value="off"
                                    {{ $detail->mixing_paddle_off ? 'checked' : '' }}>
                                <label class="form-check-label" for="mixingOff{{ $detailIndex }}">Off</label>
                            </div>
                        </div>
                        

                        <div class="col-md-6 mb-3">
                            <label>Lama Proses (menit)</label>
                            <input type="number" step="0.01" name="details[{{ $detailIndex }}][duration]"
                                class="form-control" value="{{ $detail->duration }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Pressure (Bar)</label>
                            <input type="number" step="0.01" name="details[{{ $detailIndex }}][pressure]"
                                class="form-control" value="{{ $detail->pressure }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Target Temperature (&deg;C)</label>
                            <input type="number" step="0.01" name="details[{{ $detailIndex }}][target_temperature]"
                                class="form-control" value="{{ $detail->target_temperature }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Actual Temperature (&deg;C)</label>
                            <input type="number" step="0.01" name="details[{{ $detailIndex }}][actual_temperature]"
                                class="form-control" value="{{ $detail->actual_temperature }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Status Produk</label>
                            <select name="details[{{ $detailIndex }}][product_status]" class="form-control" required>
                                <option value="Release" {{ $detail->product_status == 'Release' ? 'selected' : '' }}>Release</option>
                                <option value="Reject" {{ $detail->product_status == 'Reject' ? 'selected' : '' }}>Reject</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Tindakan Perbaikan</label>
                            <input type="text" name="details[{{ $detailIndex }}][corrective_action]" class="form-control" value="{{ $detail->corrective_action }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Catatan</label>
                            <input type="text" name="details[{{ $detailIndex }}][notes]" class="form-control"
                                value="{{ $detail->notes }}">
                        </div>

                        
                    </div>

                    <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Catatan &amp; Dokumentasi</label>
                                <textarea name="documentation_notes" class="form-control" rows="2">{{ $report->documentation_notes }}</textarea>
                            </div>
                        </div>
                </div>
                @endforeach

                <div class="mt-3">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Update</button>
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
        const getFormulasUrl = "{{ route('report_sauces.getFormulas', ['productUuid' => 'PRODUCT_UUID_PLACEHOLDER']) }}";

        formulaSelect.innerHTML = '<option value="">-- Pilih Formula --</option>';

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
    const getFormulationsUrl = "{{ route('report_sauces.getFormulations', ['formulaUuid' => 'FORMULA_UUID_PLACEHOLDER']) }}";

    if (!formulaUuid) return;

    fetch(getFormulationsUrl.replace('FORMULA_UUID_PLACEHOLDER', formulaUuid))
        .then(res => res.json())
        .then(data => {
            const buildRow = (fm, label) => `
                <div class="row mb-3 raw-material-item">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">${label} (Standard: ${fm.weight} kg)</label>
                        <input type="text" class="form-control" value="${fm.raw_material?.material_name ?? fm.premix?.name ?? '-'}" readonly>
                        <input type="hidden" name="__NAME_PREFIX__[raw_materials][${fm.uuid}][formulation_uuid]" value="${fm.uuid}">
                        
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Berat Aktual (Kg)</label>
                        <input type="number" step="0.01" name="__NAME_PREFIX__[raw_materials][${fm.uuid}][amount]" class="form-control" value="${fm.weight}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="__NAME_PREFIX__[raw_materials][${fm.uuid}][sensory]" class="form-control" required>
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tindakan Koreksi</label>
                        <input type="text" name="__NAME_PREFIX__[raw_materials][${fm.uuid}][corrective_action]" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="__NAME_PREFIX__[raw_materials][${fm.uuid}][keterangan]" class="form-control">
                    </div>
                </div>
            `;

            // formula ganti → regenerate SEMUA blok Detail Proses yang ada, karena satu report cuma pakai satu formula
            document.querySelectorAll('.raw-materials-wrapper').forEach(wrapper => {
                const detailIndex = wrapper.id.replace('raw-materials-wrapper-', '');
                const namePrefix = `details[${detailIndex}]`;
                wrapper.innerHTML = '';

                data.raw_materials.forEach(fm => wrapper.insertAdjacentHTML('beforeend', buildRow(fm, 'Bahan Baku').replaceAll('__NAME_PREFIX__', namePrefix)));
                data.premixes.forEach(fm => wrapper.insertAdjacentHTML('beforeend', buildRow(fm, 'Premix').replaceAll('__NAME_PREFIX__', namePrefix)));
            });
        });
});

function calcDuration() {
    const startVal = document.getElementById('start_time').value;
    const endVal = document.getElementById('end_time').value;
    const processInputs = document.querySelectorAll('.process-step-input');

    processInputs.forEach(processInput => {
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
    });
}

document.getElementById('start_time').addEventListener('input', calcDuration);
document.getElementById('end_time').addEventListener('input', calcDuration);
</script>
@endsection