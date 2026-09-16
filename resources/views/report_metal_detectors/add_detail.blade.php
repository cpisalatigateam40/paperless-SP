@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Kinerja Metal Detector Adonan', 'url' => route('report_metal_detectors.index')],
        ['label' => 'Tambah Detail', 'url' => null],
    ]" />

    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail Pemeriksaan ({{ $report->date }} - Shift {{ $report->shift }})</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_metal_detectors.store_detail', $report->uuid) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Produk</label>
                        <select name="product_uuid" class="form-control select2-product" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->uuid }}">{{ $product->product_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gramase</label>
                        <input type="number" step="0.01" name="gramase" class="form-control"
                            placeholder="Masukkan gramase" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label>Jam</label>
                    <input type="time" name="hour" class="form-control"
                        value="{{ \Carbon\Carbon::now()->format('H:i') }}" required>
                </div>
                <div class="mb-3">
                    <label>Kode Produksi</label>
                    <input type="text" name="production_code" class="form-control" required>
                </div>
                <div class="row">
                    <div class="mb-2 col-md-4">
                        <label class="form-label d-block">Hasil Deteksi Fe 1.5 mm</label>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="result_fe" value="√"
                                class="form-check-input" id="result_fe_ok" required checked>
                            <label class="form-check-label" for="result_fe_ok">√</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="result_fe" value="x"
                                class="form-check-input" id="result_fe_x" required >
                            <label class="form-check-label" for="result_fe_x">x</label>
                        </div>
                    </div>

                    <div class="mb-2 col-md-4">
                        <label class="form-label d-block">Hasil Deteksi Non Fe 2 mm</label>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="result_non_fe" value="√"
                                class="form-check-input" id="result_non_fe_ok" required checked>
                            <label class="form-check-label" for="result_non_fe_ok">√</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="result_non_fe" value="x"
                                class="form-check-input" id="result_non_fe_x" required >
                            <label class="form-check-label" for="result_non_fe_x">x</label>
                        </div>
                    </div>

                    <div class="mb-2 col-md-4">
                        <label class="form-label d-block">Hasil Deteksi SUS 316 2.5 mm</label>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="result_sus316" value="√"
                                class="form-check-input" id="result_sus316_ok" required checked>
                            <label class="form-check-label" for="result_sus316_ok">√</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="result_sus316" value="x"
                                class="form-check-input" id="result_sus316_x" required >
                            <label class="form-check-label" for="result_sus316_x">x</label>
                        </div>
                    </div>
                </div>
                <!-- <div class="mb-3">
                    <label>Hasil Verifikasi MD Loma</label>
                    <select name="verif_loma" class="form-control" required>
                        <option value="√">√ </option>
                        <option value="x">x </option>
                    </select>
                </div> -->
                
                <div class="row mb-3">
                    <!-- <div class="col-md-4">
                        <label>Ketidaksesuaian</label>
                        <input type="text" name="nonconformity" class="form-control">
                    </div> -->
                    <div class="col-md-4">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="verif_after_correct" value="√"
                                class="form-check-input" id="verif_after_correct_ok" checked>
                            <label class="form-check-label" for="verif_after_correct_ok">√</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="verif_after_correct" value="x"
                                class="form-check-input" id="verif_after_correct_x" >
                            <label class="form-check-label" for="verif_after_correct_x">x</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label>Tindakan Koreksi</label>
                        <input type="text" name="corrective_action" class="form-control" >
                    </div>
                    
                    <div class="col-md-4">
                        <label>Keterangan</label>
                        <textarea name="notes" class="form-control" rows="1"></textarea>
                    </div>
                </div>

                <a href="{{ route('report_metal_detectors.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-success">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection