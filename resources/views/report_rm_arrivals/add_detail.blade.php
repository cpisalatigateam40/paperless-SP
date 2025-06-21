@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Tambah Pemeriksaan untuk Laporan Tanggal
                {{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }} (Shift {{ $report->shift }})
            </h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('report_rm_arrivals.store_detail', $report->uuid) }}">
                @csrf

                <div id="detail-container">
                    <div class="detail-row mb-3 p-3 border rounded bg-light">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Bahan Baku</label>
                                <select name="details[0][raw_material_uuid]" class="form-control" required>
                                    @foreach ($rawMaterials as $material)
                                    <option value="{{ $material->uuid }}">{{ $material->material_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Kode Produksi</label>
                                <input type="text" name="details[0][production_code]" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jam</label>
                                <input type="time" name="details[0][time]" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Suhu (°C)</label>
                                <input type="number" step="0.1" name="details[0][temperature]" class="form-control">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Kemasan</label>
                                <select name="details[0][packaging_condition]" class="form-control">
                                    <option value="✓">✓</option>
                                    <option value="x">x</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sensorik</label>
                                <select name="details[0][sensorial_condition]" class="form-control">
                                    <option value="✓">✓</option>
                                    <option value="x">x</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Problem</label>
                                <textarea name="details[0][problem]" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tindakan Koreksi</label>
                                <textarea name="details[0][corrective_action]" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary" id="add-detail-btn">+ Tambah Baris
                    Pemeriksaan</button>
                <button type="submit" class="btn btn-success">Simpan Pemeriksaan</button>
                <a href="{{ route('report_rm_arrivals.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>

{{-- TEMPLATE --}}
<div id="detail-template" style="display: none;">
    <div class="detail-row mb-3 p-3 border rounded bg-light position-relative">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="form-label">Bahan Baku</label>
                <select name="details[__index__][raw_material_uuid]" class="form-control" required>
                    @foreach ($rawMaterials as $material)
                    <option value="{{ $material->uuid }}">{{ $material->material_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Kode Produksi</label>
                <input type="text" name="details[__index__][production_code]" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jam</label>
                <input type="time" name="details[__index__][time]" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Suhu</label>
                <input type="number" step="0.1" name="details[__index__][temperature]" class="form-control">
            </div>
            <div class="col-md-1">
                <label>Kemasan</label>
                <select name="details[__index__][packaging_condition]" class="form-control">
                    <option value="✓">✓</option>
                    <option value="x">x</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Sensorik</label>
                <select name="details[__index__][sensorial_condition]" class="form-control">
                    <option value="✓">✓</option>
                    <option value="x">x</option>
                </select>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <label>Problem</label>
                <textarea name="details[__index__][problem]" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-md-6">
                <label>Tindakan Koreksi</label>
                <textarea name="details[__index__][corrective_action]" class="form-control" rows="2"></textarea>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let detailIndex = 1;
document.getElementById('add-detail-btn').addEventListener('click', function() {
    const templateHtml = document.getElementById('detail-template').innerHTML;
    const rendered = templateHtml.replace(/__index__/g, detailIndex);
    const wrapper = document.createElement('div');
    wrapper.innerHTML = rendered;
    document.getElementById('detail-container').appendChild(wrapper.firstElementChild);
    detailIndex++;
});
</script>
@endsection