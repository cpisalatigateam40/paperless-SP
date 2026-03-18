@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Laporan Verifikasi Pemasakan Produk Di Steam Kettle</h5>
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
                        <select name="product_uuid" class="form-control select2-product" required>
                            <option value="">-- pilih produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->uuid }}"
                                {{ $product->uuid == $report->product_uuid ? 'selected' : '' }}>
                                {{ $product->product_name }} - {{ $product->nett_weight }} g
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Produksi</label>
                        <input type="text" name="production_code" class="form-control"
                            value="{{ $report->production_code }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Waktu Start</label>
                        <input type="time" name="start_time" class="form-control" value="{{ $report->start_time }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Waktu Stop</label>
                        <input type="time" name="end_time" class="form-control" value="{{ $report->end_time }}">
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
                        <div class="col-md-6">
                            <label class="form-label">Tahapan Proses</label>
                            <input type="text" name="details[{{ $detailIndex }}][process_step]" class="form-control"
                                value="{{ $detail->process_step }}">
                        </div>
                    </div>

                    <h6>Bahan Baku</h6>
                    <div id="raw-materials-wrapper-{{ $detailIndex }}">
                        @foreach($detail->rawMaterials as $rmIndex => $rm)
                        <div class="row mb-2 raw-material-item">
                            <div class="col-md-4">
                                <select name="details[{{ $detailIndex }}][raw_materials][{{ $rmIndex }}][material_uuid]"
                                        class="form-control"
                                        onchange="updateRmMaterialType(this)"
                                        required>
                                    <option value="">-- Pilih Bahan Baku --</option>
                                    @foreach($rawMaterials as $material)
                                        <option value="{{ $material->uuid }}" data-type="raw"
                                            {{ $rm->material_type === 'raw' && $rm->material_uuid == $material->uuid ? 'selected' : '' }}>
                                            {{ $material->material_name }}
                                        </option>
                                    @endforeach
                                    @foreach($premixes as $premix)
                                        <option value="{{ $premix->uuid }}" data-type="premix"
                                            {{ $rm->material_type === 'premix' && $rm->material_uuid == $premix->uuid ? 'selected' : '' }}>
                                            {{ $premix->name }} (Premix)
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden"
                                    name="details[{{ $detailIndex }}][raw_materials][{{ $rmIndex }}][material_type]"
                                    value="{{ $rm->material_type ?? 'raw' }}">
                            </div>
                            <div class="col-md-4">
                                <input type="number" step="0.01"
                                    name="details[{{ $detailIndex }}][raw_materials][{{ $rmIndex }}][amount]"
                                    class="form-control" value="{{ $rm->amount }}">
                            </div>
                            <div class="col-md-4">
                                <select name="details[{{ $detailIndex }}][raw_materials][{{ $rmIndex }}][sensory]"
                                        class="form-control" required>
                                    <option value="OK" {{ $rm->sensory == 'OK' ? 'selected' : '' }}>OK</option>
                                    <option value="Tidak OK" {{ $rm->sensory == 'Tidak OK' ? 'selected' : '' }}>Tidak OK</option>
                                </select>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2"
                        onclick="addRawMaterial({{ $detailIndex }})">+ Tambah Bahan Baku</button>

                    <div class="row mt-3">
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
                            <label>Catatan</label>
                            <input type="text" name="details[{{ $detailIndex }}][notes]" class="form-control"
                                value="{{ $detail->notes }}">
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="{{ route('report_sauces.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addRawMaterial(detailIndex) {
    const wrapper = document.getElementById(`raw-materials-wrapper-${detailIndex}`);
    const rmCount = wrapper.querySelectorAll('.raw-material-item').length;
    const html = `
        <div class="row mb-2 raw-material-item mt-3">
            <div class="col-md-4">
                <select name="details[${detailIndex}][raw_materials][${rmCount}][material_uuid]"
                        class="form-control"
                        onchange="updateRmMaterialType(this)"
                        required>
                    <option value="">-- Pilih Bahan Baku --</option>
                    @foreach($rawMaterials as $material)
                        <option value="{{ $material->uuid }}" data-type="raw">
                            {{ $material->material_name }}
                        </option>
                    @endforeach
                    @foreach($premixes as $premix)
                        <option value="{{ $premix->uuid }}" data-type="premix">
                            {{ $premix->name }} (Premix)
                        </option>
                    @endforeach
                </select>
                <input type="hidden"
                       name="details[${detailIndex}][raw_materials][${rmCount}][material_type]"
                       value="raw">
            </div>
            <div class="col-md-4">
                <input type="number" step="0.01"
                       name="details[${detailIndex}][raw_materials][${rmCount}][amount]"
                       class="form-control" placeholder="Berat (kg)">
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <select name="details[${detailIndex}][raw_materials][${rmCount}][sensory]"
                            class="form-control" required>
                        <option value="OK">OK</option>
                        <option value="Tidak OK">Tidak OK</option>
                    </select>
                    <button type="button" class="btn btn-danger btn-sm"
                            onclick="this.closest('.raw-material-item').remove()">✕</button>
                </div>
            </div>
        </div>`;
    wrapper.insertAdjacentHTML('beforeend', html);
}

function updateRmMaterialType(selectEl) {
    const type = selectEl.options[selectEl.selectedIndex].getAttribute('data-type') || 'raw';
    const hiddenInput = selectEl.closest('div').querySelector('input[type="hidden"]');
    if (hiddenInput) hiddenInput.value = type;
}
</script>
@endsection