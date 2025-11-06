@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Laporan Verifikasi Pembuatan Kulit Siomay, Gioza & Mandu</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('report_siomays.update', $report->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- HEADER --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::parse($report->date)->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ $report->shift }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Produk</label>
                        <select name="product_uuid" class="form-control select2-product" required>
                            <option value="">-- pilih produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->uuid }}"
                                {{ $report->product_uuid == $product->uuid ? 'selected' : '' }}>
                                {{ $product->product_name }} - {{ $product->nett_weight }} g
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control"
                            value="{{ $report->production_code }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Waktu Start</label>
                        <input type="time" name="start_time" class="form-control" value="{{ $report->start_time }}">
                    </div>
                    <div class="col-md-6">
                        <label>Waktu Stop</label>
                        <input type="time" name="end_time" class="form-control" value="{{ $report->end_time }}">
                    </div>
                </div>

                @php $detail = $report->details->first(); @endphp
                <h6 class="mt-4">Detail Proses</h6>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Pukul</label>
                        <input type="time" name="details[0][time]" class="form-control" value="{{ $detail->time }}">
                    </div>
                    <div class="col-md-6">
                        <label>Tahapan Proses</label>
                        <input type="text" name="details[0][process_step]" class="form-control"
                            value="{{ $detail->process_step }}">
                    </div>
                </div>

                {{-- RAW MATERIALS --}}
                <div id="raw-materials-wrapper">
                    @foreach($detail->rawMaterials as $i => $rm)
                    <div class="row mb-2 raw-material-item">
                        <div class="col-md-4">
                            <label>Bahan Baku</label>
                            <select name="details[0][raw_materials][{{ $i }}][raw_material_uuid]" class="form-control">
                                <option value="">-- pilih bahan baku --</option>
                                @foreach($rawMaterials as $m)
                                <option value="{{ $m->uuid }}"
                                    {{ $rm->raw_material_uuid == $m->uuid ? 'selected' : '' }}>
                                    {{ $m->material_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Berat (Kg)</label>
                            <input type="number" step="0.01" name="details[0][raw_materials][{{ $i }}][amount]"
                                class="form-control" value="{{ $rm->amount }}">
                        </div>
                        <div class="col-md-4">
                            <label>Sensory</label>
                            <select name="details[0][raw_materials][{{ $i }}][sensory]" class="form-control">
                                <option value="OK" {{ $rm->sensory == 'OK' ? 'selected' : '' }}>OK</option>
                                <option value="Tidak OK" {{ $rm->sensory == 'Tidak OK' ? 'selected' : '' }}>Tidak OK
                                </option>
                            </select>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="addRawMaterial()">+ Tambah Bahan
                    Baku</button>

                {{-- Sensory --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Warna</label>
                        <select name="details[0][color]" class="form-control">
                            <option value="OK" {{ $detail->color == 'OK' ? 'selected' : '' }}>OK</option>
                            <option value="Tidak OK" {{ $detail->color == 'Tidak OK' ? 'selected' : '' }}>Tidak OK
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Aroma</label>
                        <select name="details[0][aroma]" class="form-control">
                            <option value="OK" {{ $detail->aroma == 'OK' ? 'selected' : '' }}>OK</option>
                            <option value="Tidak OK" {{ $detail->aroma == 'Tidak OK' ? 'selected' : '' }}>Tidak OK
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Update</button>
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