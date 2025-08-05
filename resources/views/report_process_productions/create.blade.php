@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Buat Laporan Proses Produksi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_process_productions.store') }}" method="POST">
                @csrf

                {{-- HEADER --}}
                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label>Section</label>
                        <select name="section_uuid" class="form-control" required>
                            <option value="">-- Pilih Section --</option>
                            @foreach ($sections as $section)
                            <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 col-md-4">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>

                    <div class="mb-3 col-md-4">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
                    </div>
                </div>

                <hr>

                {{-- DETAIL PRODUK --}}
                <h5 class="mt-4 font-weight-bold">Detail Produk</h5>

                <div class="mb-3">
                    <label>Produk</label>
                    <select name="product_uuid" id="product-select" class="form-control" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->uuid }}" data-name="{{ $product->product_name }}">
                            {{ $product->product_name }} {{ $product->nett_weight }}
                        </option>
                        @endforeach

                    </select>
                </div>

                <div class="mb-3">
                    <label>Formula</label>
                    <select name="formula_uuid" id="formula-select" class="form-control" required>
                        <option value="">-- Pilih Formula --</option>
                        {{-- Diisi via JavaScript --}}
                    </select>
                </div>

                <div class="mb-3">
                    <label>Kode Produksi</label>
                    <input type="text" name="production_code" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Waktu Mixing</label>
                    <input type="text" name="mixing_time" class="form-control">
                </div>

                <hr>

                {{-- ITEM FORMULASI --}}
                <h5 class="mt-4 font-weight-bold">Item Formulasi</h5>
                <div id="formulation-container">
                    {{-- Diisi otomatis via JS --}}
                </div>

                <div class="row mt-4">
                    <div class="mb-3 col-md-4">
                        <label>Rework (kg)</label>
                        <input type="number" step="0.01" name="rework_kg" class="form-control">
                    </div>
                    <div class="mb-3 col-md-4">
                        <label>Rework (%)</label>
                        <input type="number" step="0.01" name="rework_percent" class="form-control">
                    </div>
                    <div class="mb-3 col-md-4">
                        <label>Total Bahan (kg)</label>
                        <input type="number" step="0.01" name="total_material" class="form-control">
                    </div>
                </div>

                <hr>

                {{-- EMULSIFYING --}}
                <h5 class="mt-4 font-weight-bold">Emulsifying</h5>
                <div class="row mb-3">
                    <div class="col">
                        <label>Suhu Standar Campuran</label>
                        <input type="text" name="standard_mixture_temp" class="form-control" value="14 ± 2">
                    </div>

                    <div class="col">
                        <label>Suhu Aktual 1</label>
                        <input type="number" step="0.1" name="actual_mixture_temp_1" id="actual_mixture_temp_1"
                            class="form-control">
                    </div>
                    <div class="col">
                        <label>Suhu Aktual 2</label>
                        <input type="number" step="0.1" name="actual_mixture_temp_2" id="actual_mixture_temp_2"
                            class="form-control">
                    </div>
                    <div class="col">
                        <label>Suhu Aktual 3</label>
                        <input type="number" step="0.1" name="actual_mixture_temp_3" id="actual_mixture_temp_3"
                            class="form-control">
                    </div>
                    <div class="col">
                        <label>Rata-rata Suhu</label>
                        <input type="text" name="average_mixture_temp" id="average_mixture_temp" class="form-control"
                            readonly>
                    </div>

                </div>

                {{-- SENSORIK --}}
                <h5 class="mt-4 font-weight-bold">Sensorik</h5>
                <div class="row mb-3">

                    <div class="col-md-3">
                        <label>Homogenitas</label>
                        <select name="homogeneous" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Kekentalan</label>
                        <select name="stiffness" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Aroma</label>
                        <select name="aroma" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Benda Asing</label>
                        <select name="foreign_object" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="Tidak Terdeteksi">Tidak Terdeteksi</option>
                            <option value="Terdeteksi">Terdeteksi</option>
                        </select>
                    </div>

                </div>

                {{-- TUMBLING --}}
                <h5 class="mt-4 font-weight-bold">Tumbling</h5>
                <div class="mb-3">
                    <label>Proses Tumbling</label>
                    <input type="text" name="tumbling_process" class="form-control">
                </div>

                {{-- AGING --}}
                <h5 class="mt-4 font-weight-bold">Aging</h5>
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label>Proses Aging</label>
                        <input type="text" name="aging_process" class="form-control">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label>Hasil Stuffing</label>
                        <input type="text" name="stuffing_result" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-3">Simpan Laporan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function initActualWeightListeners() {
    const weightInputs = document.querySelectorAll('.actual-weight');

    weightInputs.forEach(input => {
        if (input.dataset.bound === "true") return;

        const standard = parseFloat(input.dataset.standard);
        const sensorySelect = input.closest('.row').querySelector('.sensory-select');

        // Set nilai awal actual = standard
        input.value = standard.toFixed(2);

        function updateSensoryBasedOnWeight() {
            const actual = parseFloat(input.value);
            if (isNaN(actual)) {
                sensorySelect.value = "";
                return;
            }

            const deviation = Math.abs(actual - standard);
            const tolerance = standard * 0.05;

            sensorySelect.value = deviation <= tolerance ? 'OK' : 'Tidak OK';
        }

        updateSensoryBasedOnWeight();

        input.addEventListener('input', updateSensoryBasedOnWeight);

        // Tandai sudah di-bind supaya tidak double
        input.dataset.bound = "true";
    });
}

document.getElementById('product-select').addEventListener('change', function() {
    const productUuid = this.value;
    const formulaSelect = document.getElementById('formula-select');
    const formulationContainer = document.getElementById('formulation-container');

    formulaSelect.innerHTML = '<option value="">-- Pilih Formula --</option>';
    formulationContainer.innerHTML = '';

    if (!productUuid) return;

    fetch(`/report-process-productions/get-formulas/${productUuid}`)
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

document.getElementById('formula-select').addEventListener('change', function() {
    const formulaUuid = this.value;
    const container = document.getElementById('formulation-container');
    container.innerHTML = '';

    if (!formulaUuid) return;

    fetch(`/report-process-productions/get-formulations/${formulaUuid}`)
        .then(res => res.json())
        .then(data => {
            if (data.raw_materials.length) {
                container.insertAdjacentHTML('beforeend', `<h6><strong>A. BAHAN BAKU</strong></h6>`);
                data.raw_materials.forEach(fm => {
                    const html = `
                                    <div class="border p-2 mb-2">
                                        <p><strong>${fm.raw_material?.material_name ?? '-'}</strong></p>
                                        <p class="text-muted mb-2">Standard: <strong class="standard-weight">${fm.weight}</strong> kg</p>
                                        <input type="hidden" name="formulation_uuids[]" value="${fm.uuid}">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input type="number" step="0.01" 
                                                    name="actual_weight[${fm.uuid}]" 
                                                    class="form-control actual-weight" 
                                                    placeholder="Berat Aktual (kg)"
                                                    data-standard="${fm.weight}">
                                            </div>
                                            <div class="col-md-4">
                                                <select name="sensory[${fm.uuid}]" class="form-control sensory-select">
                                                    <option value="">-- Pilih --</option>
                                                    <option value="OK">OK</option>
                                                    <option value="Tidak OK">Tidak OK</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="number" step="0.1" name="temperature[${fm.uuid}]" class="form-control" placeholder="Suhu (℃)">
                                            </div>
                                        </div>
                                    </div>
                                `;
                    container.insertAdjacentHTML('beforeend', html);
                });
            }

            if (data.premixes.length) {
                container.insertAdjacentHTML('beforeend',
                    `<h6 class="mt-4"><strong>B. PREMIX / BAHAN TAMBAHAN</strong></h6>`);
                data.premixes.forEach(fm => {
                    const html = `
                            <div class="border p-2 mb-2">
                                <p><strong>${fm.premix?.name ?? '-'}</strong></p>
                                <p class="text-muted mb-2">Standard: <strong class="standard-weight">${fm.weight}</strong> kg</p>
                                <input type="hidden" name="formulation_uuids[]" value="${fm.uuid}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" 
                                            step="0.01" 
                                            name="actual_weight[${fm.uuid}]" 
                                            class="form-control actual-weight" 
                                            placeholder="Berat Aktual (kg)"
                                            data-standard="${fm.weight}">
                                    </div>
                                    <div class="col-md-4">
                                        <select name="sensory[${fm.uuid}]" class="form-control sensory-select">
                                            <option value="">-- Pilih --</option>
                                            <option value="OK">OK</option>
                                            <option value="Tidak OK">Tidak OK</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" step="0.1" name="temperature[${fm.uuid}]" class="form-control" placeholder="Suhu (℃)">
                                    </div>
                                </div>
                            </div>
                        `;
                    container.insertAdjacentHTML('beforeend', html);
                });

            }

            initActualWeightListeners();
        });
});

function hitungRataRataSuhu() {
    const temp1 = parseFloat(document.getElementById('actual_mixture_temp_1').value) || 0;
    const temp2 = parseFloat(document.getElementById('actual_mixture_temp_2').value) || 0;
    const temp3 = parseFloat(document.getElementById('actual_mixture_temp_3').value) || 0;

    const jumlah = [temp1, temp2, temp3].filter(t => t > 0).length;
    const total = temp1 + temp2 + temp3;

    const rataRata = jumlah > 0 ? (total / jumlah).toFixed(2) : '';

    document.getElementById('average_mixture_temp').value = rataRata;
}

['actual_mixture_temp_1', 'actual_mixture_temp_2', 'actual_mixture_temp_3'].forEach(id => {
    document.getElementById(id).addEventListener('input', hitungRataRataSuhu);
});

document.getElementById('product-select').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const productName = selectedOption.getAttribute('data-name');

    if (!productName) return;

    fetch(`/report-process-productions/get-formulas-by-name?product_name=${encodeURIComponent(productName)}`)
        .then(res => res.json())
        .then(data => {
            const formulaSelect = document.getElementById('formula-select');
            formulaSelect.innerHTML = '<option value="">-- Pilih Formula --</option>';
            data.forEach(formula => {
                const option = document.createElement('option');
                option.value = formula.uuid;
                option.textContent = formula.formula_name;
                formulaSelect.appendChild(option);
            });
        });
});
</script>
@endsection