@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Laporan Proses Produksi</h4>
        </div>
        <div class="card-body">

            @php
            $detail = $report->detail->first();
            @endphp

            <form action="{{ route('report_process_productions.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- HEADER --}}
                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label>Section</label>
                        <select name="section_uuid" class="form-control" required>
                            <option value="">-- Pilih Section --</option>
                            @foreach ($sections as $section)
                            <option value="{{ $section->uuid }}"
                                {{ old('section_uuid', $report->section_uuid) == $section->uuid ? 'selected' : '' }}>
                                {{ $section->section_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 col-md-4">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', $report->date) }}"
                            required>
                    </div>

                    <div class="mb-3 col-md-4">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ old('shift', $report->shift) }}"
                            required>
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
                        <option value="{{ $product->uuid }}"
                            {{ old('product_uuid', $detail->product_uuid) == $product->uuid ? 'selected' : '' }}>
                            {{ $product->product_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Formula</label>
                    <select name="formula_uuid" id="formula-select" class="form-control" required>
                        <option value="">-- Pilih Formula --</option>
                        @foreach($formulas as $formula)
                        <option value="{{ $formula->uuid }}"
                            {{ old('formula_uuid', $detail->formula_uuid) == $formula->uuid ? 'selected' : '' }}>
                            {{ $formula->formula_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Kode Produksi</label>
                    <input type="text" name="production_code" class="form-control"
                        value="{{ old('production_code', $detail->production_code) }}">
                </div>

                <div class="mb-3">
                    <label>Waktu Mixing</label>
                    <input type="text" name="mixing_time" class="form-control"
                        value="{{ old('mixing_time', $detail->mixing_time) }}">
                </div>

                <hr>

                {{-- ITEM FORMULASI --}}
                <h5 class="mt-4 font-weight-bold">Item Formulasi</h5>
                @foreach($detail->items as $item)
                <div class="border p-2 mb-2">
                    <p><strong>{{ $item->formulation?->rawMaterial?->material_name ?? $item->formulation?->premix?->name ?? '-' }}</strong>
                    </p>
                    <p class="text-muted mb-2">Standard: <strong>{{ $item->formulation?->weight }} gr</strong></p>
                    <input type="hidden" name="formulation_uuids[]" value="{{ $item->formulation_uuid }}">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="number" step="0.01" name="actual_weight[{{ $item->formulation_uuid }}]"
                                class="form-control"
                                value="{{ old('actual_weight.'.$item->formulation_uuid, $item->actual_weight) }}"
                                placeholder="Berat Aktual (gr)">
                        </div>
                        <div class="col-md-4">
                            <select name="sensory[{{ $item->formulation_uuid }}]" class="form-control">
                                <option value="">-- Pilih --</option>
                                <option value="OK"
                                    {{ old('sensory.'.$item->formulation_uuid, $item->sensory) == 'OK' ? 'selected' : '' }}>
                                    OK</option>
                                <option value="Tidak OK"
                                    {{ old('sensory.'.$item->formulation_uuid, $item->sensory) == 'Tidak OK' ? 'selected' : '' }}>
                                    Tidak OK</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="number" step="0.1" name="temperature[{{ $item->formulation_uuid }}]"
                                class="form-control"
                                value="{{ old('temperature.'.$item->formulation_uuid, $item->temperature) }}"
                                placeholder="Suhu (℃)">
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="row mt-4">
                    <div class="mb-3 col-md-4">
                        <label>Rework (kg)</label>
                        <input type="number" step="0.01" name="rework_kg" class="form-control"
                            value="{{ old('rework_kg', $detail->rework_kg) }}">
                    </div>
                    <div class="mb-3 col-md-4">
                        <label>Rework (%)</label>
                        <input type="number" step="0.01" name="rework_percent" class="form-control"
                            value="{{ old('rework_percent', $detail->rework_percent) }}">
                    </div>
                    <div class="mb-3 col-md-4">
                        <label>Total Bahan (kg)</label>
                        <input type="number" step="0.01" name="total_material" class="form-control"
                            value="{{ old('total_material', $detail->total_material) }}">
                    </div>
                </div>

                <hr>

                {{-- EMULSIFYING --}}
                <h5 class="mt-4 font-weight-bold">Emulsifying</h5>
                <div class="row mb-3">
                    <div class="col">
                        <label>Suhu Standar Campuran</label>
                        <input type="text" name="standard_mixture_temp" class="form-control"
                            value="{{ old('standard_mixture_temp', $detail->emulsifying->standard_mixture_temp) }}">
                    </div>
                    <div class="col">
                        <label>Suhu Aktual 1</label>
                        <input type="number" step="0.1" name="actual_mixture_temp_1" id="actual_mixture_temp_1"
                            class="form-control"
                            value="{{ old('actual_mixture_temp_1', $detail->emulsifying->actual_mixture_temp_1) }}">
                    </div>
                    <div class="col">
                        <label>Suhu Aktual 2</label>
                        <input type="number" step="0.1" name="actual_mixture_temp_2" id="actual_mixture_temp_2"
                            class="form-control"
                            value="{{ old('actual_mixture_temp_2', $detail->emulsifying->actual_mixture_temp_2) }}">
                    </div>
                    <div class="col">
                        <label>Suhu Aktual 3</label>
                        <input type="number" step="0.1" name="actual_mixture_temp_3" id="actual_mixture_temp_3"
                            class="form-control"
                            value="{{ old('actual_mixture_temp_3', $detail->emulsifying->actual_mixture_temp_3) }}">
                    </div>
                    <div class="col">
                        <label>Rata-rata Suhu</label>
                        <input type="text" name="average_mixture_temp" id="average_mixture_temp" class="form-control"
                            value="{{ old('average_mixture_temp', $detail->emulsifying->average_mixture_temp) }}"
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
                            <option value="OK"
                                {{ old('homogeneous', $detail->sensoric->homogeneous)=='OK' ? 'selected' : '' }}>OK
                            </option>
                            <option value="Tidak OK"
                                {{ old('homogeneous', $detail->sensoric->homogeneous)=='Tidak OK' ? 'selected' : '' }}>
                                Tidak OK</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Kekentalan</label>
                        <select name="stiffness" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="OK"
                                {{ old('stiffness', $detail->sensoric->stiffness)=='OK' ? 'selected' : '' }}>OK</option>
                            <option value="Tidak OK"
                                {{ old('stiffness', $detail->sensoric->stiffness)=='Tidak OK' ? 'selected' : '' }}>Tidak
                                OK</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Aroma</label>
                        <select name="aroma" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="OK" {{ old('aroma', $detail->sensoric->aroma)=='OK' ? 'selected' : '' }}>OK
                            </option>
                            <option value="Tidak OK"
                                {{ old('aroma', $detail->sensoric->aroma)=='Tidak OK' ? 'selected' : '' }}>Tidak OK
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Benda Asing</label>
                        <select name="foreign_object" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="Tidak Terdeteksi"
                                {{ old('foreign_object', $detail->sensoric->foreign_object)=='Tidak Terdeteksi' ? 'selected' : '' }}>
                                Tidak Terdeteksi</option>
                            <option value="Terdeteksi"
                                {{ old('foreign_object', $detail->sensoric->foreign_object)=='Terdeteksi' ? 'selected' : '' }}>
                                Terdeteksi</option>
                        </select>
                    </div>
                </div>

                {{-- TUMBLING --}}
                <h5 class="mt-4 font-weight-bold">Tumbling</h5>
                <div class="mb-3">
                    <label>Proses Tumbling</label>
                    <input type="text" name="tumbling_process" class="form-control"
                        value="{{ old('tumbling_process', $detail->tumbling->tumbling_process) }}">
                </div>

                {{-- AGING --}}
                <h5 class="mt-4 font-weight-bold">Aging</h5>
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label>Proses Aging</label>
                        <input type="text" name="aging_process" class="form-control"
                            value="{{ old('aging_process', $detail->aging->aging_process) }}">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label>Hasil Stuffing</label>
                        <input type="text" name="stuffing_result" class="form-control"
                            value="{{ old('stuffing_result', $detail->aging->stuffing_result) }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-3">Update Laporan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
// Hitung rata-rata suhu otomatis
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
</script>
@endsection