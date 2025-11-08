@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Tambah Laporan Verifikasi Pembuatan Kulit Siomay, Gioza & Mandu</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('report_siomays.store') }}" method="POST">
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
                        <input type="text" id="shift" name="shift" class="form-control" required>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Produk</label>
                        <select name="product_uuid" class="form-control select2-product" required>
                            <option value="">-- pilih produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->uuid }}">{{ $product->product_name }} -
                                {{ $product->nett_weight }} g</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control">
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Waktu Start</label>
                        <input type="time" name="start_time" class="form-control"
                            value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Waktu Stop</label>
                        <input type="time" name="end_time" class="form-control"
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
                    <div class="col-md-6">
                        <label class="form-label">Tahapan Proses</label>
                        <input type="text" name="details[0][process_step]" class="form-control">
                    </div>
                </div>

                {{-- RAW MATERIALS --}}
                <div id="raw-materials-wrapper">
                    <div class="row mb-2 raw-material-item">
                        <div class="col-md-4">
                            <label class="form-label">Bahan Baku</label>
                            <select name="details[0][raw_materials][0][raw_material_uuid]" class="form-control"
                                required>
                                <option value="">-- pilih bahan baku --</option>
                                @foreach($rawMaterials as $rm)
                                <option value="{{ $rm->uuid }}">{{ $rm->material_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Berat (kg)</label>
                            <input type="number" step="0.01" name="details[0][raw_materials][0][amount]"
                                class="form-control" placeholder="Berat (kg)">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sensory</label>
                            <select name="details[0][raw_materials][0][sensory]" class="form-control" required>
                                <option value="OK">OK</option>
                                <option value="Tidak OK">Tidak OK</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="addRawMaterial()">+ Tambah Bahan
                    Baku</button>



                {{-- DETAIL PROSES (1x input, bukan repeater) --}}





                <div class="row mb-2">
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
                        <input type="number" step="0.01" name="details[0][duration]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pressure (Bar)</label>
                        <input type="number" step="0.01" name="details[0][pressure]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Target Temperature (&deg;C)</label>
                        <input type="number" step="0.01" name="details[0][target_temperature]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Actual Temperature (&deg;C)</label>
                        <input type="number" step="0.01" name="details[0][actual_temperature]" class="form-control">
                    </div>
                </div>


                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="details[0][notes]" class="form-control">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="{{ route('report_siomays.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let rmIndex = 1;

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
                <select name="details[0][raw_materials][${rmIndex}][sensory]" class="form-control" required>
                    <option value="OK">OK</option>
                    <option value="Tidak OK">Tidak OK</option>
                </select>
            </div>
        </div>
        `;
    wrapper.insertAdjacentHTML('beforeend', html);
    rmIndex++;
}
</script>
@endsection