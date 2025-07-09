@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Report Metal Detector</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_metal_detectors.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>
                <div class="mb-3">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
                </div>
                <div class="mb-3">
                    <label>Section</label>
                    <select name="section_uuid" class="form-control">
                        <option value="">-- Pilih Section --</option>
                        @foreach($sections as $section)
                        <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                        @endforeach
                    </select>
                </div>

                <hr>
                <h5>Detail Pemeriksaan</h5>
                <div id="details">
                    <div class="card p-3 mb-2">
                        <div class="mb-2">
                            <label>Produk</label>
                            <select name="details[0][product_uuid]" class="form-control" required>
                                @foreach($products as $product)
                                <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label>Jam</label>
                            <input type="time" name="details[0][hour]" class="form-control"
                                value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>
                        </div>

                        <div class="mb-2">
                            <label>Kode Produksi</label>
                            <input type="text" name="details[0][production_code]" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="mb-2 col-md-4">
                                <label>Hasil Deteksi Fe 1.5 mm</label>
                                <select name="details[0][result_fe]" class="form-control" required>
                                    <option value="√">√ (Terdeteksi)</option>
                                    <option value="x">x (Tidak terdeteksi)</option>
                                </select>
                            </div>
                            <div class="mb-2 col-md-4">
                                <label>Hasil Deteksi Non Fe 2 mm</label>
                                <select name="details[0][result_non_fe]" class="form-control" required>
                                    <option value="√">√ (Terdeteksi)</option>
                                    <option value="x">x (Tidak terdeteksi)</option>
                                </select>
                            </div>
                            <div class="mb-2 col-md-4">
                                <label>Hasil Deteksi SUS 316 2.5 mm</label>
                                <select name="details[0][result_sus316]" class="form-control" required>
                                    <option value="√">√ (Terdeteksi)</option>
                                    <option value="x">x (Tidak terdeteksi)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label>Keterangan</label>
                            <textarea name="details[0][notes]" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary" onclick="addDetail()">+ Tambah
                    Detail</button>

                <button type="submit" class="btn btn-success">Simpan</button>
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
    <div class="card p-3 mb-2">
        <div class="mb-2">
            <label>Produk</label>
            <select name="details[${detailIndex}][product_uuid]" class="form-control" required>
                @foreach($products as $product)
                <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-2">
            <label>Jam</label>
            <input type="time" name="details[${detailIndex}][hour]" class="form-control"
                   value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>
        </div>
        <div class="mb-2">
            <label>Kode Produksi</label>
            <input type="text" name="details[${detailIndex}][production_code]" class="form-control" required>
        </div>
        <div class="row">
            <div class="mb-2 col-md-4">
                <label>Hasil Deteksi Fe 1.5 mm</label>
                <select name="details[${detailIndex}][result_fe]" class="form-control" required>
                    <option value="√">√ (Terdeteksi)</option>
                    <option value="x">x (Tidak terdeteksi)</option>
                </select>
            </div>
            <div class="mb-2 col-md-4">
                <label>Hasil Deteksi Non Fe 2 mm</label>
                <select name="details[${detailIndex}][result_non_fe]" class="form-control" required>
                    <option value="√">√ (Terdeteksi)</option>
                    <option value="x">x (Tidak terdeteksi)</option>
                </select>
            </div>
            <div class="mb-2 col-md-4">
                <label>Hasil Deteksi SUS 316 2.5 mm</label>
                <select name="details[${detailIndex}][result_sus316]" class="form-control" required>
                    <option value="√">√ (Terdeteksi)</option>
                    <option value="x">x (Tidak terdeteksi)</option>
                </select>
            </div>
        </div>
        <div class="mb-2">
            <label>Keterangan</label>
            <textarea name="details[${detailIndex}][notes]" class="form-control"></textarea>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">Hapus Detail</button>
    </div>
    `;
    document.getElementById('details').insertAdjacentHTML('beforeend', html);
    detailIndex++;
}
</script>
@endsection