@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Buat Laporan Verifikasi Proses Produksi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_process_productions.store') }}" method="POST">
                @csrf

                {{-- HEADER --}}
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ session('shift_number') }}-{{ session('shift_group') }}" required>
                    </div>
                </div>

                <hr>

                {{-- DETAIL PRODUK --}}
                <h5 class="mt-4 font-weight-bold">Detail Produk</h5>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label>Produk</label>
                        <select name="product_uuid" id="product-select" class="form-control select2" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $product)
                            <option value="{{ $product->uuid }}" data-name="{{ $product->product_name }}">
                                {{ $product->product_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Gramase</label>
                        <input type="number" step="0.01" name="gramase" class="form-control"
                            placeholder="Masukkan gramase" required>
                    </div>

                    <div class="col-md-6">
                        <label>Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control" placeholder="mis: QE12171AA0">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label>Formula</label>
                        <select name="formula_uuid" id="formula-select" class="form-control" required>
                            <option value="">-- Pilih Formula --</option>
                            {{-- Diisi via JavaScript --}}
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Waktu Mixing (Menit)</label>
                        <input type="text" name="mixing_time" class="form-control" placeholder="mis: 6">
                    </div>

                    <div class="col-md-6">
                        <label>Nama Mesin Mixer/Chopper</label>
                        <input type="text" name="machine_name" class="form-control" placeholder="Nama Mesin Mixer/Chopper">
                    </div>
                </div>

                <hr>

                {{-- ITEM FORMULASI --}}
                <h5 class="mt-4 font-weight-bold">Item Formulasi</h5>
                <div id="formulation-container">
                    {{-- Diisi otomatis via JS --}}
                </div>

                <datalist id="prod-code-list"></datalist>

                <h5 class="mt-4 font-weight-bold">Hasil Proses</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label>Hasil Penggilingan</label>
                        <input type="text" name="hasil_penggilingan" class="form-control" placeholder="masukkan hasil penggilingan">
                    </div>
                    <div class="col-md-6">
                        <label>Hasil Pencampuran</label>
                        <input type="text" name="hasil_pencampuran" class="form-control" placeholder="masukkan hasil pencampuran">
                    </div>
                </div>

                <h5 class="mt-4 font-weight-bold">Penggunaan Rework</h5>
                <div class=" row mt-4">
                    <div class="mb-3 col-md-3">
                        <label>Rework (kg)</label>
                        <input type="number" step="0.01" name="rework_kg" class="form-control" placeholder="mis: 6">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Produk Rework</label>
                        <select name="rework_product_uuid" class="form-control select2">
                            <option value="">-- Pilih Produk Rework --</option>
                            @foreach ($products as $product)
                            <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 col-md-3">
                        <label>Rework (%)</label>
                        <input type="number" step="0.01" name="rework_percent" class="form-control" placeholder="mis: 6">
                    </div>
                    <div class="mb-3 col-md-3">
                        <label>Total Bahan (kg)</label>
                        <input type="number" step="0.01" name="total_material" class="form-control" placeholder="mis: 6">
                    </div>
                </div>

                <!-- <div class="row">
                    <div class="col-md-4">
                        <label>Sensori Homogenitas</label>
                        <select name="sensory_homogenity" class="form-control">

                            <option value="√">√</option>
                            <option value="x">x</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Sensori Kekentalan</label>
                        <select name="sensory_stiffness" class="form-control">

                            <option value="√">√</option>
                            <option value="x">x</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Sensori Aroma</label>
                        <select name="sensory_aroma" class="form-control">

                            <option value="√">√</option>
                            <option value="x">x</option>
                        </select>
                    </div>
                </div> -->

                <div class="row mt-3">
                    <div class="col-md-12">
                        <label>Catatan After Rework</label>
                        <textarea name="detail_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <hr>

                {{-- EMULSIFYING --}}
                <h5 class="mt-4 font-weight-bold">Emulsifying</h5>
                <div class="row mb-3">
                    <div class="col">
                        <label>Suhu Standar Campuran (°C)</label>
                        <input type="text" name="standard_mixture_temp" class="form-control" value="14 ± 2">
                    </div>

                    <div class="col">
                        <label>Suhu Aktual 1 (°C)</label>
                        <input type="number" step="0.1" name="actual_mixture_temp_1" id="actual_mixture_temp_1"
                            class="form-control" placeholder="mis: 6">
                    </div>
                    <div class="col">
                        <label>Suhu Aktual 2 (°C)</label>
                        <input type="number" step="0.1" name="actual_mixture_temp_2" id="actual_mixture_temp_2"
                            class="form-control" placeholder="mis: 6">
                    </div>
                    <div class="col">
                        <label>Suhu Aktual 3 (°C)</label>
                        <input type="number" step="0.1" name="actual_mixture_temp_3" id="actual_mixture_temp_3"
                            class="form-control" placeholder="mis: 6">
                    </div>
                    <div class="col">
                        <label>Rata-rata Suhu (°C)</label>
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

                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Kekentalan</label>
                        <select name="stiffness" class="form-control">

                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Aroma</label>
                        <select name="aroma" class="form-control">

                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Benda Asing</label>
                        <select name="foreign_object" class="form-control">

                            <option value="Tidak Terdeteksi">Tidak Terdeteksi</option>
                            <option value="Terdeteksi">Terdeteksi</option>
                        </select>
                    </div>
                </div>

                {{-- TUMBLING --}}
                <h5 class="mt-4 font-weight-bold">Tumbling</h5>
                <div class="row">
                    <div class=" col-md-4 mb-3">
                        <label>Proses Tumbling</label>
                        <input type="text" name="tumbling_process" class="form-control" placeholder="masukkan proses tumbling">
                    </div>
                    <div class="mb-3 col-md-4">
                        <label>Lama Proses (Menit)</label>
                        <input type="number" step="1" name="process_duration" class="form-control" placeholder="mis: 3">
                    </div>
                    <div class="mb-3 col-md-4">
                        <label>Suhu Akhir Tumbling (°C)</label>
                        <input type="number" step="0.1" name="final_temperature" class="form-control" placeholder="mis: 12.5">
                    </div>
                </div>
                

                {{-- AGING --}}
                <h5 class="mt-4 font-weight-bold">Aging</h5>
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label>Proses Aging</label>
                        <input type="text" name="aging_process" class="form-control" placeholder="masukkan proses aging">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label>Hasil Stuffing</label>
                        <input type="text" name="stuffing_result" class="form-control" placeholder="masukkan hasil stuffing">
                    </div>
                </div>

                {{-- CATATAN REPORT --}}
                <h5 class="mt-4 font-weight-bold">Catatan</h5>
                <div class="mb-3">
                    <label>Catatan Umum</label>
                    <textarea name="report_notes" class="form-control" rows="3"></textarea>
                </div>

                <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Kembali</a>
                <button type="submit" class="btn btn-success mt-3">Simpan</button>
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

        const standard = input.dataset.standard;
        const sensorySelect = input.closest('.row').querySelector('.sensory-select');

        // Set nilai awal actual = standard
        input.value = standard;

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
    const getFormulasUrl = @json(route('report_process_productions.getFormulas', ['productUuid' =>
        'PRODUCT_UUID_PLACEHOLDER'
    ]));

    formulaSelect.innerHTML = '<option value="">-- Pilih Formula --</option>';
    formulationContainer.innerHTML = '';

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
        })
});

document.getElementById('formula-select').addEventListener('change', function() {
    const formulaUuid = this.value;
    const container = document.getElementById('formulation-container');
    const getFormulationsUrl = @json(route('report_process_productions.getFormulations', ['formulaUuid' =>
        'FORMULA_UUID_PLACEHOLDER'
    ]));

    container.innerHTML = '';

    if (!formulaUuid) return;

    fetch(getFormulationsUrl.replace('FORMULA_UUID_PLACEHOLDER', formulaUuid))
        .then(res => res.json())
        .then(data => {
            if (data.raw_materials.length) {
                container.insertAdjacentHTML('beforeend', `<h6><strong>A. BAHAN BAKU</strong></h6>`);
                data.raw_materials.forEach((fm, index) => {
                    const html = `
            <div class="border p-2 mb-2">
                <p><strong>${index + 1}. ${fm.raw_material?.material_name ?? '-'}</strong></p>
                <p class="text-muted mb-2">Standard: <strong class="standard-weight">${fm.weight}</strong> kg</p>
                <input type="hidden" name="formulation_uuids[]" value="${fm.uuid}">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="number"
                            step="0.00001"
                            name="actual_weight[${fm.uuid}]" 
                            class="form-control actual-weight" 
                            placeholder="Berat Aktual (kg)"
                            data-standard="${fm.weight}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <select name="sensory[${fm.uuid}]" class="form-control sensory-select">
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <input type="text" name="prod_code[${fm.uuid}]" class="form-control" list="prod-code-list" placeholder="Kode Produksi">
                    </div>
                    <div class="col-md-4 mb-3">
                        <input type="number" step="0.1" name="temperature[${fm.uuid}]" class="form-control" placeholder="Suhu (℃)">
                    </div>
                    <div class="col-md-4 mb-3">
                        <input type="text" name="keterangan[${fm.uuid}]" class="form-control" placeholder="Keterangan">
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
                data.premixes.forEach((fm, index) => {
                    const html = `
            <div class="border p-2 mb-2">
                <p><strong>${index + 1}. ${fm.premix?.name ?? '-'}</strong></p>
                <p class="text-muted mb-2">Standard: <strong class="standard-weight">${fm.weight}</strong> kg</p>
                <input type="hidden" name="formulation_uuids[]" value="${fm.uuid}">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="number" step="0.00001" 
                            name="actual_weight[${fm.uuid}]" 
                            class="form-control actual-weight" 
                            placeholder="Berat Aktual (kg)"
                            data-standard="${fm.weight}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <select name="sensory[${fm.uuid}]" class="form-control sensory-select">
                            <option value="">-- Pilih --</option>
                            <option value="OK">OK</option>
                            <option value="Tidak OK">Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <input type="text" name="prod_code[${fm.uuid}]" class="form-control" list="prod-code-list" placeholder="Kode Produksi">
                    </div>
                    <div class="col-md-4 mb-3">
                        <input type="number" step="0.1" name="temperature[${fm.uuid}]" class="form-control" placeholder="Suhu (℃)">
                    </div>
                    <div class="col-md-4 mb-3">
                        <input type="text" name="keterangan[${fm.uuid}]" class="form-control" placeholder="Keterangan">
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

document.addEventListener('DOMContentLoaded', function () {
    fetch(@json(route('report_process_productions.getProdCodeSuggestions')))
        .then(res => res.json())
        .then(codes => {
            const datalist = document.getElementById('prod-code-list');
            codes.forEach(code => {
                const opt = document.createElement('option');
                opt.value = code;
                datalist.appendChild(opt);
            });
        });
});
</script>
@endsection