@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail Laporan Proses Produksi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_process_productions.store_detail', $report->uuid) }}" method="POST">
                @csrf

                {{-- PRODUK --}}
                <div class="mb-3">
                    <label>Produk</label>
                    <select name="product_uuid" id="product-select" class="form-control" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- FORMULA --}}
                <div class="mb-3">
                    <label>Formula</label>
                    <select name="formula_uuid" id="formula-select" class="form-control" required>
                        <option value="">-- Pilih Formula --</option>
                    </select>
                </div>

                {{-- KODE PRODUKSI --}}
                <div class="mb-3">
                    <label>Kode Produksi</label>
                    <input type="text" name="production_code" class="form-control">
                </div>

                {{-- WAKTU MIXING --}}
                <div class="mb-3">
                    <label>Waktu Mixing</label>
                    <input type="text" name="mixing_time" class="form-control">
                </div>

                {{-- ITEM FORMULASI --}}
                <h5 class="mt-4 font-weight-bold">Item Formulasi</h5>
                <div id="formulation-container">
                    {{-- Diisi otomatis oleh JavaScript --}}
                </div>

                {{-- REWORK & TOTAL --}}
                <div class="row mt-4">
                    <div class="col-md-4">
                        <label>Rework (kg)</label>
                        <input type="number" step="0.01" name="rework_kg" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Rework (%)</label>
                        <input type="number" step="0.01" name="rework_percent" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Total Bahan (kg)</label>
                        <input type="number" step="0.01" name="total_material" class="form-control">
                    </div>
                </div>

                {{-- EMULSIFYING --}}
                <hr>
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
                <hr>
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
                            <option value="Tidak Terdeteksi">Tidak Terdeteksi</option>
                            <option value="Terdeteksi">Terdeteksi</option>
                        </select>
                    </div>
                </div>

                {{-- TUMBLING --}}
                <hr>
                <h5 class="mt-4 font-weight-bold">Tumbling</h5>
                <div class="mb-3">
                    <label>Proses Tumbling</label>
                    <input type="text" name="tumbling_process" class="form-control">
                </div>

                {{-- AGING --}}
                <hr>
                <h5 class="mt-4 font-weight-bold">Aging</h5>
                <div class="row">
                    <div class="col-md-6">
                        <label>Proses Aging</label>
                        <input type="text" name="aging_process" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Hasil Stuffing</label>
                        <input type="text" name="stuffing_result" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-4">Simpan Detail</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
// AJAX Produk → Formula
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

// AJAX Formula → Formulasi
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
                            <p class="text-muted mb-2">Standard: <strong>${fm.weight} gr</strong></p>
                            <input type="hidden" name="formulation_uuids[]" value="${fm.uuid}">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="number" step="0.01" name="actual_weight[${fm.uuid}]" class="form-control" placeholder="Berat Aktual (gr)">
                                </div>
                                <div class="col-md-4">
                                    <select name="sensory[${fm.uuid}]" class="form-control">
                                        <option value="">-- Pilih --</option>
                                        <option value="OK">OK</option>
                                        <option value="Tidak OK">Tidak OK</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" step="0.1" name="temperature[${fm.uuid}]" class="form-control" placeholder="Suhu (℃)">
                                </div>
                            </div>
                        </div>`;
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
                            <p class="text-muted mb-2">Standard: <strong>${fm.weight} gr</strong></p>
                            <input type="hidden" name="formulation_uuids[]" value="${fm.uuid}">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="number" step="0.01" name="actual_weight[${fm.uuid}]" class="form-control" placeholder="Berat Aktual (gr)">
                                </div>
                                <div class="col-md-4">
                                    <select name="sensory[${fm.uuid}]" class="form-control">
                                        <option value="">-- Pilih --</option>
                                        <option value="OK">OK</option>
                                        <option value="Tidak OK">Tidak OK</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" step="0.1" name="temperature[${fm.uuid}]" class="form-control" placeholder="Suhu (℃)">
                                </div>
                            </div>
                        </div>`;
                    container.insertAdjacentHTML('beforeend', html);
                });
            }
        });
});

// Hitung Rata-rata Suhu
function hitungRataRataSuhu() {
    const t1 = parseFloat(document.getElementById('actual_mixture_temp_1').value) || 0;
    const t2 = parseFloat(document.getElementById('actual_mixture_temp_2').value) || 0;
    const t3 = parseFloat(document.getElementById('actual_mixture_temp_3').value) || 0;

    const count = [t1, t2, t3].filter(v => v > 0).length;
    const total = t1 + t2 + t3;

    document.getElementById('average_mixture_temp').value = count > 0 ? (total / count).toFixed(2) : '';
}

['actual_mixture_temp_1', 'actual_mixture_temp_2', 'actual_mixture_temp_3'].forEach(id => {
    document.getElementById(id).addEventListener('input', hitungRataRataSuhu);
});
</script>
@endsection