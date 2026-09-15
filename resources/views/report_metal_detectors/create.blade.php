@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Kinerja Metal Detector Adonan', 'url' => route('report_metal_detectors.index')],
        ['label' => 'Tambah Data', 'url' => null],
    ]" />

    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Verifikasi Kinerja Metal Detector Adonan</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_metal_detectors.store') }}" method="POST">
                @csrf

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
                    <!-- <div class="col-md-6">
                        <label>Section</label>
                        <select name="section_uuid" class="form-control">
                            <option value="">-- Pilih Section --</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div> -->
                </div>
                

                <hr>
                <h5>Detail Pemeriksaan</h5>
                <div id="details">
                    <div class="card p-3 mb-5">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Produk</label>
                                <select name="details[0][product_uuid]" class="form-control select2-product" required>
                                    @foreach($products as $product)
                                    <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gramase</label>
                                <input type="number" step="0.01" name="details[0][gramase]" class="form-control"
                                    placeholder="Masukkan gramase" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label>Jam</label>
                            <input type="time" name="details[0][hour]" class="form-control"
                                value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>
                        </div>

                        <div class="mb-3">
                            <label>Kode Produksi</label>
                            <input type="text" name="details[0][production_code]" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-4">
                                <label class="form-label d-block">Hasil Deteksi Fe 1.5 mm</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[0][result_fe]" value="√"
                                        class="form-check-input" id="result_fe_ok_0" required checked>
                                    <label class="form-check-label" for="result_fe_ok_0">√</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[0][result_fe]" value="x"
                                        class="form-check-input" id="result_fe_x_0" required >
                                    <label class="form-check-label" for="result_fe_x_0">x</label>
                                </div>
                            </div>

                            <div class="mb-3 col-md-4">
                                <label class="form-label d-block">Hasil Deteksi Non Fe 2mm</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[0][result_non_fe]" value="√"
                                        class="form-check-input" id="result_non_fe_ok_0" required checked>
                                    <label class="form-check-label" for="result_non_fe_ok_0">√</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[0][result_non_fe]" value="x"
                                        class="form-check-input" id="result_non_fe_x_0" required >
                                    <label class="form-check-label" for="result_non_fe_x_0">x</label>
                                </div>
                            </div>

                            <div class="mb-3 col-md-4">
                                <label class="form-label d-block">Hasil Deteksi SUS 316 2.5 mm</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[0][result_sus316]" value="√"
                                        class="form-check-input" id="result_sus316_ok_0" required checked>
                                    <label class="form-check-label" for="result_sus316_ok_0">√</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[0][result_sus316]" value="x"
                                        class="form-check-input" id="result_sus316_x_0" required >
                                    <label class="form-check-label" for="result_sus316_x_0">x</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[0][verif_after_correct]" value="√"
                                        class="form-check-input" id="verif_after_correct_ok_0" checked>
                                    <label class="form-check-label" for="verif_after_correct_ok_0">√</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="details[0][verif_after_correct]" value="x"
                                        class="form-check-input" id="verif_after_correct_x_0">
                                    <label class="form-check-label" for="verif_after_correct_x_0">x</label>
                                </div>
                            </div>
                            <!-- <div class="col-md-4">
                                <label>Ketidaksesuaian</label>
                                <input type="text" name="details[0][nonconformity]" class="form-control">
                            </div> -->
                            <div class="col-md-4">
                                <label>Tindakan Koreksi</label>
                                <input type="text" name="details[0][corrective_action]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Keterangan</label>
                                <textarea name="details[0][notes]" class="form-control" rows="1"></textarea>
                            </div>
                            
                        </div>
                        
                    </div>
                </div>

                <div class="form-group mt-5 mb-5">
                    <label>Notes</label>
                    <textarea
                        name="notes"
                        class="form-control"
                        rows="3"
                        placeholder="Masukkan catatan apabila diperlukan">{{ old('notes') }}</textarea>
                </div>
                <button type="button" class="btn btn-outline-secondary" onclick="addDetail()">+ Tambah
                    Detail</button>

                    <div class="mt-3">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let detailIndex = 1;

function addDetail() {
    let html = `
        <div class="card p-3 mb-3">
            <div class="row">
                <div class="mb-3 col-md-6">
                    <label>Produk</label>
                    <select name="details[${detailIndex}][product_uuid]" class="form-control" required>
                        @foreach($products as $product)
                            <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gramase</label>
                    <input type="number" step="0.01" name="details[${detailIndex}][gramase]" class="form-control"
                        placeholder="Masukkan gramase" required>
                </div>
            </div>
            <div class="mb-3">
                <label>Jam</label>
                <input type="time" name="details[${detailIndex}][hour]" class="form-control"
                       value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>
            </div>
            <div class="mb-3">
                <label>Kode Produksi</label>
                <input type="text" name="details[${detailIndex}][production_code]" class="form-control" required>
            </div>
            <div class="row">
                <div class="mb-3 col-md-4">
                    <label class="form-label d-block">Hasil Deteksi Fe 1.5 mm</label>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="details[${detailIndex}][result_fe]" value="√"
                            class="form-check-input" id="result_fe_ok_${detailIndex}" required checked>
                        <label class="form-check-label" for="result_fe_ok_${detailIndex}">√</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="details[${detailIndex}][result_fe]" value="x"
                            class="form-check-input" id="result_fe_x_${detailIndex}" required >
                        <label class="form-check-label" for="result_fe_x_${detailIndex}">x</label>
                    </div>
                </div>
                <div class="mb-3 col-md-4">
                    <label class="form-label d-block">Hasil Deteksi Non Fe 2 mm</label>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="details[${detailIndex}][result_non_fe]" value="√"
                            class="form-check-input" id="result_non_fe_ok_${detailIndex}" required checked>
                        <label class="form-check-label" for="result_non_fe_ok_${detailIndex}">√</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="details[${detailIndex}][result_non_fe]" value="x"
                            class="form-check-input" id="result_non_fe_x_${detailIndex}" required >
                        <label class="form-check-label" for="result_non_fe_x_${detailIndex}">x</label>
                    </div>
                </div>
                <div class="mb-3 col-md-4">
                    <label class="form-label d-block">Hasil Deteksi SUS 316 2.5 mm</label>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="details[${detailIndex}][result_sus316]" value="√"
                            class="form-check-input" id="result_sus316_ok_${detailIndex}" required checked>
                        <label class="form-check-label" for="result_sus316_ok_${detailIndex}">√</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="details[${detailIndex}][result_sus316]" value="x"
                            class="form-check-input" id="result_sus316_x_${detailIndex}" required >
                        <label class="form-check-label" for="result_sus316_x_${detailIndex}">x</label>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label d-block">Status</label>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="details[${detailIndex}][verif_after_correct]" value="√"
                            class="form-check-input" id="verif_after_correct_ok_${detailIndex}" required checked>
                        <label class="form-check-label" for="verif_after_correct_ok_${detailIndex}">√</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="details[${detailIndex}][verif_after_correct]" value="x"
                            class="form-check-input" id="verif_after_correct_x_${detailIndex}" required>
                        <label class="form-check-label" for="verif_after_correct_x_${detailIndex}">x</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label>Tindakan Koreksi</label>
                    <input type="text" name="details[${detailIndex}][corrective_action]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Keterangan</label>
                    <textarea name="details[${detailIndex}][notes]" class="form-control" rows="1"></textarea>
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">Hapus Detail</button>
        </div>
        `;
    document.getElementById('details').insertAdjacentHTML('beforeend', html);
    detailIndex++;
}
</script>
@endsection