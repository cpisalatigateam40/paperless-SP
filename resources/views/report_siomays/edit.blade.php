@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Proses Pembuatan Kulit Siomay/Gyoza', 'url' => route('report_siomays.index')],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Verifikasi Proses Pembuatan Kulit Siomay/Gyoza</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('report_siomays.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- HEADER REPORT --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ old('date', \Carbon\Carbon::parse($report->date)->toDateString()) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" id="shift" name="shift" class="form-control"
                            value="{{ old('shift', $report->shift) }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label>Produk</label>
                        <select name="product_uuid" class="form-control select2-product" required>
                            <option value="">-- pilih produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->uuid }}"
                                {{ old('product_uuid', $report->product_uuid) == $product->uuid ? 'selected' : '' }}>
                                {{ $product->product_name }}
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
                    <div class="col-md-6">
                        <label class="form-label">Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control"
                            value="{{ old('production_code', $report->production_code) }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Waktu Start</label>
                        <input type="time" name="start_time" class="form-control"
                            value="{{ old('start_time', $report->start_time) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Waktu Stop</label>
                        <input type="time" name="end_time" class="form-control"
                            value="{{ old('end_time', $report->end_time) }}">
                    </div>
                </div>

                <h6 class="mt-4">Detail Proses</h6>

                @foreach($report->details as $idx => $detail)
                <div class="border rounded p-3 mb-4">
                    <h6 class="text-muted">Detail Proses #{{ $idx + 1 }}</h6>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Pukul</label>
                            <input type="time" name="details[{{ $idx }}][time]" class="form-control"
                                value="{{ old("details.$idx.time", $detail->time) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahapan Proses</label>
                            <input type="text" name="details[{{ $idx }}][process_step]" class="form-control"
                                value="{{ old("details.$idx.process_step", $detail->process_step) }}">
                        </div>
                    </div>

                    {{-- RAW MATERIALS --}}
                    <div class="raw-materials-wrapper" data-detail-index="{{ $idx }}">
                        @foreach($detail->rawMaterials as $i => $rmDetail)
                        <div class="row mb-2 raw-material-item">
                            <div class="col-md-4">
                                <label class="form-label">Bahan Baku</label>
                                <select name="details[{{ $idx }}][raw_materials][{{ $i }}][raw_material_uuid]" class="form-control" required>
                                    <option value="">-- pilih bahan baku --</option>
                                    @foreach($rawMaterials as $rm)
                                    <option value="{{ $rm->uuid }}"
                                        {{ $rmDetail->raw_material_uuid == $rm->uuid ? 'selected' : '' }}>
                                        {{ $rm->material_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Berat (kg)</label>
                                <input type="number" step="0.01" name="details[{{ $idx }}][raw_materials][{{ $i }}][amount]"
                                    value="{{ old("details.$idx.raw_materials.$i.amount", $rmDetail->amount) }}"
                                    class="form-control" placeholder="Berat (kg)">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">Sensory</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $idx }}][raw_materials][{{ $i }}][sensory]" value="OK"
                                        class="form-check-input" id="rm_sensory_ok_{{ $idx }}_{{ $i }}" required
                                        {{ $rmDetail->sensory == 'OK' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="rm_sensory_ok_{{ $idx }}_{{ $i }}">OK</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[{{ $idx }}][raw_materials][{{ $i }}][sensory]" value="Tidak OK"
                                        class="form-check-input" id="rm_sensory_x_{{ $idx }}_{{ $i }}" required
                                        {{ $rmDetail->sensory == 'Tidak OK' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="rm_sensory_x_{{ $idx }}_{{ $i }}">Tidak OK</label>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-3 mb-3">
                            <label class="form-label d-block">Warna</label>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="details[{{ $idx }}][color]" value="OK"
                                    class="form-check-input" id="color_ok_{{ $idx }}" required
                                    {{ $detail->color == 'OK' ? 'checked' : '' }}>
                                <label class="form-check-label" for="color_ok_{{ $idx }}">OK</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="details[{{ $idx }}][color]" value="Tidak OK"
                                    class="form-check-input" id="color_x_{{ $idx }}" required
                                    {{ $detail->color == 'Tidak OK' ? 'checked' : '' }}>
                                <label class="form-check-label" for="color_x_{{ $idx }}">Tidak OK</label>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label d-block">Aroma</label>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="details[{{ $idx }}][aroma]" value="OK"
                                    class="form-check-input" id="aroma_ok_{{ $idx }}" required
                                    {{ $detail->aroma == 'OK' ? 'checked' : '' }}>
                                <label class="form-check-label" for="aroma_ok_{{ $idx }}">OK</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="details[{{ $idx }}][aroma]" value="Tidak OK"
                                    class="form-check-input" id="aroma_x_{{ $idx }}" required
                                    {{ $detail->aroma == 'Tidak OK' ? 'checked' : '' }}>
                                <label class="form-check-label" for="aroma_x_{{ $idx }}">Tidak OK</label>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label d-block">Rasa</label>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="details[{{ $idx }}][taste]" value="OK"
                                    class="form-check-input" id="taste_ok_{{ $idx }}" required
                                    {{ $detail->taste == 'OK' ? 'checked' : '' }}>
                                <label class="form-check-label" for="taste_ok_{{ $idx }}">OK</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="details[{{ $idx }}][taste]" value="Tidak OK"
                                    class="form-check-input" id="taste_x_{{ $idx }}" required
                                    {{ $detail->taste == 'Tidak OK' ? 'checked' : '' }}>
                                <label class="form-check-label" for="taste_x_{{ $idx }}">Tidak OK</label>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label d-block">Tekstur</label>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="details[{{ $idx }}][texture]" value="OK"
                                    class="form-check-input" id="texture_ok_{{ $idx }}" required
                                    {{ $detail->texture == 'OK' ? 'checked' : '' }}>
                                <label class="form-check-label" for="texture_ok_{{ $idx }}">OK</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="details[{{ $idx }}][texture]" value="Tidak OK"
                                    class="form-check-input" id="texture_x_{{ $idx }}" required
                                    {{ $detail->texture == 'Tidak OK' ? 'checked' : '' }}>
                                <label class="form-check-label" for="texture_x_{{ $idx }}">Tidak OK</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 mt-4">
                        <div class="col-md-6">
                            <label class="form-label d-block">Mixing Paddle</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="details[{{ $idx }}][mixing_paddle]" value="on"
                                    id="mixingOn{{ $idx }}" {{ $detail->mixing_paddle_on ? 'checked' : '' }}>
                                <label class="form-check-label" for="mixingOn{{ $idx }}">On</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="details[{{ $idx }}][mixing_paddle]" value="off"
                                    id="mixingOff{{ $idx }}" {{ $detail->mixing_paddle_off ? 'checked' : '' }}>
                                <label class="form-check-label" for="mixingOff{{ $idx }}">Off</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lama Proses (menit)</label>
                            <input type="number" step="0.01" name="details[{{ $idx }}][duration]" class="form-control"
                                value="{{ old("details.$idx.duration", $detail->duration) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pressure (Bar)</label>
                            <input type="number" step="0.01" name="details[{{ $idx }}][pressure]" class="form-control"
                                value="{{ old("details.$idx.pressure", $detail->pressure) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Temperature (&deg;C)</label>
                            <input type="number" step="0.01" name="details[{{ $idx }}][target_temperature]" class="form-control"
                                value="{{ old("details.$idx.target_temperature", $detail->target_temperature) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Actual Temperature (&deg;C)</label>
                            <input type="number" step="0.01" name="details[{{ $idx }}][actual_temperature]" class="form-control"
                                value="{{ old("details.$idx.actual_temperature", $detail->actual_temperature) }}">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="details[{{ $idx }}][notes]" class="form-control"
                                value="{{ old("details.$idx.notes", $detail->notes) }}">
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

<script>
let rmIndex = {{ $detail->rawMaterials->count() }};

function addRawMaterial() {
    let wrapper = document.getElementById('raw-materials-wrapper');
    let html = `
        <div class="row mb-2 raw-material-item">
            <div class="col-md-4">
                <select name="details[0][raw_materials][${rmIndex}][raw_material_uuid]" class="form-control" required>
                    <option value="">-- pilih bahan baku --</option>
                    @foreach($rawMaterials as $rm)
                        <option value="{{ $rm->uuid }}">{{ $rm->material_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" step="0.01" name="details[0][raw_materials][${rmIndex}][amount]" class="form-control"
                       placeholder="Berat (kg)">
            </div>
            <div class="col-md-4">
                <label class="form-label d-block">Sensory</label>
                <div class="form-check form-check-inline">
                    <input type="radio" name="details[0][raw_materials][${rmIndex}][sensory]" value="OK"
                        class="form-check-input" id="rm_sensory_ok_${rmIndex}" required checked>
                    <label class="form-check-label" for="rm_sensory_ok_${rmIndex}">OK</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" name="details[0][raw_materials][${rmIndex}][sensory]" value="Tidak OK"
                        class="form-check-input" id="rm_sensory_x_${rmIndex}" required>
                    <label class="form-check-label" for="rm_sensory_x_${rmIndex}">Tidak OK</label>
                </div>
            </div>
        </div>
        `;
    wrapper.insertAdjacentHTML('beforeend', html);
    rmIndex++;
}
</script>
@endsection