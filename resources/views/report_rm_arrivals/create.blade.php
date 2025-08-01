@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Tambah Laporan Kedatangan Bahan Baku</h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('report_rm_arrivals.store') }}">
                @csrf

                <div class="row mb-5">
                    <div class="col-md-4">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>
                    <div class="col-md-4">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
                    </div>

                    <div class="col-md-4">
                        <label>Section</label>
                        <select name="section_uuid" class="form-control" required>
                            <option value="">-- Pilih Section --</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h5>Detail Pemeriksaan</h5>
                <div id="detail-container">
                    {{-- Baris pertama default --}}
                    <div class="detail-row mb-3 p-3 border rounded bg-light">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Bahan Baku</label>
                                <select name="details[0][raw_material_uuid]" class="form-control raw-material-select"
                                    required>
                                    <option value="">-- Pilih --</option>
                                    @foreach ($rawMaterials as $material)
                                    <option value="{{ $material->uuid }}" data-supplier="{{ $material->supplier }}">
                                        {{ $material->material_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Produsen</label>
                                <input type="text" name="details[0][supplier]" class="form-control supplier-input">
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
                            <div class="col-md-1">
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
                            <div class="col-md-1">
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
                                <textarea name="details[0][problem]" class="form-control" rows="2"
                                    placeholder="Jika ada masalah, tulis di sini..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tindakan Koreksi</label>
                                <textarea name="details[0][corrective_action]" class="form-control" rows="2"
                                    placeholder="Langkah yang dilakukan..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Tombol Tambah --}}
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-detail-btn">
                    + Tambah Pemeriksaan
                </button>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- TEMPLATE TERSEMBUNYI --}}
<div id="detail-template" style="display: none;">
    <div class="detail-row mb-3 p-3 border rounded bg-light">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="form-label">Bahan Baku</label>
                <select name="details[__index__][raw_material_uuid]" class="form-control raw-material-select" required>
                    <option value="">-- Pilih --</option>
                    @foreach ($rawMaterials as $material)
                    <option value="{{ $material->uuid }}" data-supplier="{{ $material->supplier }}">
                        {{ $material->material_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Produsen</label>
                <input type="text" name="details[__index__][supplier]" class="form-control supplier-input">
            </div>
            <div class="col-md-2">
                <label class="form-label">Kode Produksi</label>
                <input type="text" name="details[__index__][production_code]" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jam</label>
                <input type="time" name="details[__index__][time]" class="form-control"
                    value="{{ \Carbon\Carbon::now()->format('H:i') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label">Suhu (°C)</label>
                <input type="number" step="0.1" name="details[__index__][temperature]" class="form-control">
            </div>
            <div class="col-md-1">
                <label class="form-label">Kemasan</label>
                <select name="details[__index__][packaging_condition]" class="form-control">
                    <option value="✓">✓</option>
                    <option value="x">x</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Sensorik</label>
                <select name="details[__index__][sensorial_condition]" class="form-control">
                    <option value="✓">✓</option>
                    <option value="x">x</option>
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label">Problem</label>
                <textarea name="details[__index__][problem]" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tindakan Koreksi</label>
                <textarea name="details[__index__][corrective_action]" class="form-control" rows="2"></textarea>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
let detailIndex = 1;

// document.getElementById('add-detail-btn').addEventListener('click', function() {
//     const template = document.getElementById('detail-template').innerHTML;
//     const newRowHtml = template.replace(/__index__/g, detailIndex);
//     const container = document.getElementById('detail-container');

//     const wrapper = document.createElement('div');
//     wrapper.innerHTML = newRowHtml;
//     container.appendChild(wrapper.firstElementChild);

//     detailIndex++;
// });

document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk inisialisasi event listener
    function initRawMaterialSelectEvent(context) {
        context.querySelectorAll('.raw-material-select').forEach(function(select) {
            select.addEventListener('change', function() {
                const supplier = this.selectedOptions[0].dataset.supplier || '';
                const parentRow = this.closest('.detail-row');
                const supplierInput = parentRow.querySelector('.supplier-input');
                supplierInput.value = supplier;
            });
        });
    }

    // Init awal untuk row default
    initRawMaterialSelectEvent(document);

    // Contoh jika ada tombol tambah detail
    document.getElementById('add-detail-btn')?.addEventListener('click', function() {
        const container = document.getElementById('detail-container');
        const template = document.getElementById('detail-template').innerHTML;
        const index = container.querySelectorAll('.detail-row').length;
        const html = template.replace(/__index__/g, index);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html.trim();
        const newRow = tempDiv.firstChild;
        container.appendChild(newRow);
        // Init event di row baru
        initRawMaterialSelectEvent(newRow);
    });
});
</script>
@endsection