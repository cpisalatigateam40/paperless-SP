@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail Pemeriksaan ({{ $report->date }} - Shift {{ $report->shift }})</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_metal_detectors.store_detail', $report->uuid) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Produk</label>
                    <select name="product_uuid" class="form-control" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
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
                        <label>Hasil Deteksi Fe 1.5 mm</label>
                        <select name="result_fe" class="form-control" required>
                            <option value="√">√ (Terdeteksi)</option>
                            <option value="x">x (Tidak terdeteksi)</option>
                        </select>
                    </div>
                    <div class="mb-2 col-md-4">
                        <label>Hasil Deteksi Non Fe 2 mm</label>
                        <select name="result_non_fe" class="form-control" required>
                            <option value="√">√ (Terdeteksi)</option>
                            <option value="x">x (Tidak terdeteksi)</option>
                        </select>
                    </div>
                    <div class="mb-2 col-md-4">
                        <label>Hasil Deteksi SUS 316 2.5 mm</label>
                        <select name="result_sus316" class="form-control" required>
                            <option value="√">√ (Terdeteksi)</option>
                            <option value="x">x (Tidak terdeteksi)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Keterangan</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>

                <button type="submit" class="btn btn-success">Simpan Detail</button>
                <a href="{{ route('report_metal_detectors.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection